## RUNBOOK.md

## Why this exists / when to use it

- You need a “zero → tests passing” path that doesn’t rely on tribal knowledge.
- You’re debugging a behavior in production/staging and need to locate the correct hook/handler fast.
- You’re preparing a release and must follow the repo’s encoded deploy automation.
- You want **smoke tests** you can actually run (or explicit **Unknown** when the repo doesn’t encode it).

## What this repo is (operationally)

This is a **WordPress plugin** (not a standalone service). The plugin’s boot chain is:

- `plugin.php` → requires `simple-urls.php`
  - Evidence: `plugin.php` contains `require_once plugin_dir_path( __FILE__ ) . '/simple-urls.php';`
- `simple-urls.php` defines constants, loads `admin/constant.php` + autoloaders, then instantiates:
  - legacy CPT/redirect (`new Simple_Urls()` from `includes/class-simple-urls.php`)
  - modern wiring (`new Init()` from `classes/class-init.php`)
  - Evidence: `simple-urls.php` contains `new Simple_Urls(); ... new Init();`

## Prerequisites (provable from repo)

- **WordPress**: “Requires at least 5.1”
  - Evidence: `readme.txt` header `Requires at least: 5.1`
- **PHP**:
  - Plugin metadata says `Requires PHP: 7.2` (WordPress.org field)
    - Evidence: `readme.txt` header `Requires PHP: 7.2`
  - Composer allows `^5.3 || ^7`
    - Evidence: `composer.json` `"php": "^5.3 || ^7"`
  - Tests do **not** run on PHP 8+ (bootstrap exits)
    - Evidence: `tests/bootstrap.php`:
      - `if ( PHP_MAJOR_VERSION >= 8 ) { ... exit( 1 ); }`
  - CI uses PHP 7.4 (CircleCI image)
    - Evidence: `.circleci/config.yml` uses `cimg/php:7.4.26`
- **Composer**: used for installing PHP deps
  - Evidence: `.circleci/config.yml` runs `composer update` and `composer install`
- **Database for tests**: MySQL/MariaDB
  - Evidence: `.circleci/config.yml` uses `cimg/mariadb:10.6` alongside the PHP image and runs `bin/install-wp-tests.sh ...`
- **Node (optional)**: only for i18n scripts
  - Evidence: `package.json` scripts `makepot` / `addtextdomain` using `node-wp-i18n`

## Bootstrap (dev dependencies)

- Install PHP deps (for local tooling/tests):

```bash
composer install
```

Evidence this is needed:
- `phpunit.xml.dist` expects running tests via PHPUnit with a WP test bootstrap.
- `.circleci/config.yml` installs dependencies via Composer before running PHPCS/unit tests.

- Optional (only if touching translations):

```bash
npm install
```

Evidence: `package.json` contains the translation scripts.

## Run locally (plugin in WordPress)

**Unknown**: the repo does not include a docker-compose, wp-env config, or “local WordPress” instructions.

What is provable:
- This is a WordPress plugin and must be activated inside a WordPress install.
  - Evidence: `readme.txt` “Installing Simple URLs is quick…” describes WP admin install/activation flow.

**How to confirm your local runtime setup**:
- Find your local WordPress root and ensure this repo is present under `wp-content/plugins/`.
- Activate the plugin, then confirm the CPT exists:
  - Evidence: `includes/class-simple-urls.php` registers post type slug `surl` on `init`.

## Run tests locally (mirror CI)

This repo encodes CI in **CircleCI** and **Travis**. Copy one of those if you want “works like CI”.

### Golden path (CircleCI-equivalent)

1) Install PHP deps:

```bash
composer install
```

2) Install a compatible PHPUnit (CircleCI uses a global install) and set up the WP test DB:

- Evidence: `.circleci/config.yml` `unit_test` runs:
  - `composer global require "phpunit/phpunit=7.5.20"`
  - `bash bin/install-wp-tests.sh wordpress_test root '' 127.0.0.1 latest`
  - `phpunit --coverage-text --log-junit build/junit.xml`

3) Run PHPUnit:

```bash
phpunit
```

Evidence:
- `phpunit.xml.dist` uses `bootstrap="tests/bootstrap.php"`.
- `tests/bootstrap.php` aborts unless `WP_TESTS_DIR/includes/functions.php` exists and suggests running `bin/install-wp-tests.sh`.
- `.circleci/config.yml` shows one working way to provision the WP test DB (`bin/install-wp-tests.sh ...`), then run `phpunit`.

### Common failures (and what to check)

- **“Could not find …/includes/functions.php”**:
  - Evidence: `tests/bootstrap.php` prints this exact message and exits.
  - Fix: run the WP test bootstrap script the way CI does (see `.circleci/config.yml` `unit_test` which runs `bash bin/install-wp-tests.sh ...`).

## Code standards (PHPCS)

CI runs WordPress Coding Standards across the main PHP code.

```bash
vendor/bin/phpcs --standard=WordPress -p -v includes/ classes/ models/ pages/
```

Evidence: `.circleci/config.yml` `wpcs` step runs PHPCS across `includes/`, `classes/`, `models/`, and `pages/`.

Alternative (narrower):

```bash
composer run phpcs
```

Evidence: `composer.json` defines script `phpcs` (limited to `includes/class-simple-urls.php` and `includes/class-simple-urls-admin.php`).

## Smoke tests (actionable)

### Smoke 1: Cloaked link redirects (301) and increments click count

What you’re validating:
- Redirect URL comes from `_surl_redirect`.
- Click count increments `_surl_count`.
- Redirect status is 301 (or 302 to home if redirect missing).

Evidence: `includes/class-simple-urls.php` `count_and_redirect()`:
- `update_post_meta( ... '_surl_count', $count + 1 )`
- `get_post_meta(..., '_surl_redirect', true )`
- `wp_redirect( ..., 301 )` else `wp_safe_redirect( home_url(), 302 )`

How to run (manual):
- In WP admin, create a new post of type **Lasso Lite URL** (`surl`) and set its redirect URL (metabox uses `_surl_redirect`).
  - Evidence: `includes/class-simple-urls-admin.php` metabox saves `_surl_redirect`.
- Then request the cloaked URL in a browser or with curl:

```bash
curl -I "http://<your-site>/go/<your-surl-slug>/"
```

Notes:
- The rewrite slug default is `go` but filterable.
  - Evidence: `includes/class-simple-urls.php` `$rewrite_slug_default = 'go'` and `apply_filters( 'simple_urls_slug', ...)`.

### Smoke 2: `[lasso id="..."]` renders the display container

Evidence:
- `pages/class-hook.php` registers the shortcode `[lasso]`.
- `classes/class-shortcode.php` renders it for `surl` posts.
- `tests/classes/LassoLiteShortCodeTest.php` asserts output contains `<div id="lasso-lite-anchor-id-...">`.

How to run:
- Insert `[lasso id="<surl_post_id>"]` into a post/page and view it.
- Optional programmatic check:

```bash
php -r 'echo "Run inside WP context: do_shortcode(\"[lasso id=\\\"123\\\"]\")\\n";'
```

**Unknown**: a local WP CLI/script runner is not provided by this repo; use your environment’s WordPress tooling.

### Smoke 3: Admin AJAX endpoint responds (nonce + access required)

Evidence:
- AJAX handlers call `Helper::verify_access_and_nonce(...)` (e.g. `pages/class-ajax.php`).
- Nonce expected is `wp_verify_nonce( $nonce, Constant::LASSO_LITE_NONCE . wp_salt() )`.
  - Evidence: `classes/class-helper.php` `verify_access_and_nonce()`.
- The nonce value is passed into JS as `lassoLiteOptionsData.optionsNonce`.
  - Evidence: `pages/class-hook.php` `optionsNonce => wp_create_nonce( Constant::LASSO_LITE_NONCE . wp_salt() )`.

How to run:
- Log into WP admin as an admin user.
- In browser console, read `lassoLiteOptionsData.optionsNonce`.
- Call `admin-ajax.php` with an action that exists, e.g. `lasso_lite_get_display_html` (registered in `pages/class-ajax.php`).

## Debug playbook (where to look first)

### Redirect is wrong / not firing

- Check CPT registration + rewrite slug: `includes/class-simple-urls.php` `register_post_type()`.
- Check redirect handler: `includes/class-simple-urls.php` `count_and_redirect()` (only runs when `is_singular('surl')`).
- Check filters: `simple_urls_redirect_url` may mutate the final URL (notably Amazon rewriting in `pages/class-hook.php` `lasso_lite_redirect()`).

### Admin AJAX returns “Access denied” or “Nonce not verified”

- Verify handler calls `Helper::verify_access_and_nonce()` and you meet capability requirements.
  - Evidence: `classes/class-helper.php` requires `manage_options` unless `$allow_edit_post_access` allows `editor/author/contributor`.
- Ensure request includes `nonce=<lassoLiteOptionsData.optionsNonce>`.
  - Evidence: `pages/class-hook.php` localizes `optionsNonce` and `classes/class-helper.php` reads `$data['nonce']`.

### External API failures (license/support)

- HTTP plumbing: `classes/class-helper.php` `send_request()` uses WP HTTP API and returns `status_code` + JSON-decoded `response`.
- License check: `classes/class-license.php` calls `${LASSO_LINK}/license/status`.
- Support enable: `classes/class-setting.php` posts JWT payload to `${LASSO_LINK}/lasso-lite/enable-support`.
- Base URL constant: `admin/constant.php` `Constant::LASSO_LINK`.

### DB/table errors

- Table creation/versioning: `classes/class-update-db.php`
- Table definitions: `models/class-*.php`
- DB query wrapper logs certain errors and may recreate tables:
  - Evidence: `classes/class-lasso-db.php` `log_error()` calls `Update_DB::create_tables()` when it sees “Illegal mix of collations” or “Unknown column”, then `trigger_error(...)`.

## Deploy / release (encoded automation)

### WordPress.org deploy (on GitHub Release publish)

- Evidence: `.github/workflows/main.yml` runs on `release: types: [published]` and uses `10up/action-wordpress-plugin-deploy@stable`.
- Required secrets (names only):
  - `SVN_USERNAME`
  - `SVN_PASSWORD`

### RC zip + S3 upload (on push to `master`)

- Evidence: `.github/workflows/release-rc.yml` runs on push to `master`, minifies CSS via `python minify_css.py`, removes dev-only files, then zips and uploads to S3.
- Required secrets (names only):
  - `S3_BUCKET_LASSO_LITE_RC`
  - `S3_BUCKET_LASSO_LITE_PUBLIC`
  - `AWS_ACCESS_KEY_ID`
  - `AWS_SECRET_ACCESS_KEY`

