## AGENTS.md

This repo is a WordPress plugin; “running the app” means **running WordPress with this plugin installed**.

## Why this exists / when to use it

- You’re an engineer or agent making changes to this plugin and want the **fastest safe path** to: find the right entrypoints, make a small change, prove it works, and not ship regressions.
- Use this before you touch code: it points to the actual boot chain, test runner, and CI/deploy contracts **as encoded in the repo**.
- It’s optimized for **Cursor-style TDD**: smallest diff, failing test first, quick feedback, then tighten via lint/standards.
- It avoids “tribal knowledge”: when something isn’t provable from the repo, it’s explicitly marked **Unknown** and tells you what to check.

## Repo reality (don’t fight it)

- **Plugin entrypoint**: WordPress loads `plugin.php`, which defines `LASSO_LITE_VERSION` and `require_once ... '/simple-urls.php'`.
  - Evidence: `plugin.php` contains `define( 'LASSO_LITE_VERSION', '132' );` and `require_once plugin_dir_path( __FILE__ ) . '/simple-urls.php';`
- **Bootstrap chain**: `simple-urls.php` defines constants, requires `admin/constant.php`, `autoload.php`, the vendor autoloader under `vendor-prefix/`, then instantiates the legacy CPT/redirect classes and the newer init system.
  - Evidence: `simple-urls.php` contains `define( "SIMPLE_URLS_SLUG", "surl" );` and `new Simple_Urls(); ... new Init();`
- **Primary runtime surfaces**:
  - **CPT + redirect**: `includes/class-simple-urls.php` registers CPT `surl` (rewrite slug default `go`) and redirects in `template_redirect`.
  - **Shortcode**: `pages/class-hook.php` registers `[lasso]`; implementation lives in `classes/class-shortcode.php`.
  - **Admin UI / AJAX**: “new UI” is driven by `pages/**/class-ajax.php` + `admin/views/**`.
  - **Cron/jobs**: `classes/class-cron.php` schedules `lasso_lite_*` events.

## Guardrails (agent safety)

- **Do not paste secrets** into PRs/docs/issues. This repo contains values that look secret-ish (e.g. JWT key, Sentry DSN). Treat them as sensitive even if committed.
  - Evidence: `admin/constant.php` defines `SENTRY_LITE_DSN` and `Constant::JWT_SECRET_KEY` (values **must be treated as sensitive**).
- **Prefer editing first-party code only**:
  - **OK**: `classes/`, `includes/`, `models/`, `pages/`, `admin/views/`, `admin/assets/`, `tests/`
  - **Avoid**: `vendor-prefix/vendor/**` (vendored deps) unless the task is explicitly “patch vendored code”.
    - Evidence: `vendor-prefix/vendor/**` is checked in (see tracked files list via `git ls-files`).
- **Keep changes small and reversible**:
  - “One behavior change” per diff.
  - Add tests for bugfixes and logic changes.
- **Honor WordPress norms**: hooks/filters are public API. If you change a hook name or filter contract, call it out and add a compat path if possible.
  - Evidence: `includes/class-simple-urls.php` uses `apply_filters( 'simple_urls_redirect_url', ...)` and `do_action( 'simple_urls_redirect', ...)`.

## Cursor TDD workflow (golden path)

### 1) Reproduce via a failing test

- **Test suites live in** `tests/` and `tests/classes/`.
  - Evidence: `phpunit.xml.dist` includes:
    - `bootstrap="tests/bootstrap.php"`
    - test discovery in `./tests/` (suffix `Test.php`) and `./tests/classes/` (suffix `.php`)
- **Plugin test bootstrap** manually loads the plugin file.
  - Evidence: `tests/bootstrap.php` defines `_manually_load_plugin()` which `require ... '/simple-urls.php';`

### 2) Run tests locally (match CI)

CI is the “truth” for how tests are expected to run. In this repo, CI is encoded in **CircleCI** (and a legacy Travis config).

- **Unit tests** are run via `phpunit` after provisioning the WP test DB.
  - Evidence: `.circleci/config.yml` `unit_test` step runs:
    - `bash bin/install-wp-tests.sh wordpress_test root '' 127.0.0.1 latest`
    - `phpunit --coverage-text --log-junit build/junit.xml`
- **Tests do not run on PHP 8+** (bootstrap exits).
  - Evidence: `tests/bootstrap.php` exits when `PHP_MAJOR_VERSION >= 8`.

If you need local commands, copy the CI steps in `.circleci/config.yml` under the `unit_test` command block.

### 3) Tighten with code standards (PHPCS)

- CI runs PHPCS across `includes/ classes/ models/ pages/`.
  - Evidence: `.circleci/config.yml` `wpcs` step runs PHPCS across those paths.
- There’s also a narrower composer script that checks just two legacy files.
  - Evidence: `composer.json` script `phpcs` runs PHPCS on `./includes/class-simple-urls.php` and `./includes/class-simple-urls-admin.php`.

## Where to put new tests

- **Legacy CPT/redirect behavior**: add to `tests/LassoSimpleURLsTest.php` (covers `Simple_Urls`).
  - Evidence: `tests/LassoSimpleURLsTest.php` instantiates `new Simple_Urls()` and tests `register_post_type()`, `use_amazon_url_instead_of_cloaked()`, etc.
- **Admin metabox behavior**: `tests/LassoSimpleURLsAdminTest.php`.
  - Evidence: `tests/LassoSimpleURLsAdminTest.php` instantiates `new Simple_Urls_Admin()`.
- **Shortcode behavior**: `tests/classes/LassoLiteShortCodeTest.php`.
  - Evidence: that test asserts `[lasso id="..."]` output contains `<div id="lasso-lite-anchor-id-...">`.

## Common change patterns (with entrypoints)

### Change redirect behavior (the cloaked link)

- **Core redirect is here**: `includes/class-simple-urls.php` → `count_and_redirect()`.
  - Evidence: `includes/class-simple-urls.php` reads `_surl_redirect` meta and `wp_redirect(..., 301)` on `template_redirect`.
- **Amazon-specific redirect rewriting**: `pages/class-hook.php` filters `simple_urls_redirect_url` via `lasso_lite_redirect()`.
  - Evidence: `pages/class-hook.php` contains `add_filter( 'simple_urls_redirect_url', ... 'lasso_lite_redirect' ...)` and inside checks `Amazon_Api::is_amazon_url(...)`.

### Add/modify an admin AJAX endpoint

- **AJAX endpoints are registered** in `pages/**/class-ajax.php` via `add_action( 'wp_ajax_...' , ...)`.
  - Evidence: `pages/class-ajax.php` registers actions like `wp_ajax_lasso_lite_add_a_new_link`.
- **Auth+nonce gate is centralized**: `classes/class-helper.php` → `verify_access_and_nonce(...)`.
  - Evidence: `classes/class-helper.php` requires `current_user_can('manage_options')` (or limited roles if `$allow_edit_post_access`) and verifies `nonce` against `Constant::LASSO_LITE_NONCE . wp_salt()`.
- **Nonce is provided to JS** as `lassoLiteOptionsData.optionsNonce`.
  - Evidence: `pages/class-hook.php` sets `optionsNonce => wp_create_nonce( Constant::LASSO_LITE_NONCE . wp_salt() )`.

### Add a new setting / default

- **Defaults** live in `admin/constant.php` (`Constant::DEFAULT_SETTINGS`).
- **Storage** is the WP option `lassolite_settings`.
  - Evidence: `classes/class-setting.php` uses `get_option( 'lassolite_settings', ... )` and `update_option( 'lassolite_settings', ...)`.

### Add/change DB schema

- The plugin has **custom tables** managed via model classes and `dbDelta`.
  - Evidence:
    - `classes/class-update-db.php` calls `Amazon_Products::create_table()`, `Url_Details::create_table()`, `Revert::create_table()`
    - Each model’s `create_table()` builds `CREATE TABLE ...` (e.g. `models/class-url-details.php`)
    - `models/class-model.php` uses `dbDelta( $sql )` (see grep match in this repo)

### Add/adjust cron/background jobs

- Cron schedule/handlers: `classes/class-cron.php`
  - Evidence: defines `CRONS` mapping and schedules with `wp_schedule_event(...)`.
- Long-running processes appear under `classes/processes/**` (instantiated in `classes/class-init.php`).
  - Evidence: `classes/class-init.php` lists processes like `\LassoLite\Classes\Processes\Import_All`.

## Definition of Done (PR checklist)

- [ ] **Scope**: one focused change; no drive-by refactors.
- [ ] **Tests**: add/adjust tests under `tests/` and run PHPUnit (match `.circleci/config.yml`).
- [ ] **Standards**: run PHPCS using the same standard as CI (`WordPress`).
- [ ] **No secrets**: don’t print or paste DSNs/keys/tokens (even if present in repo).
- [ ] **Backward compatibility**: don’t break WP hooks/filters without a clear migration path.
- [ ] **Docs**: if your change alters how to run/test/release, update `RUNBOOK.md` / `SKILLS.md`.

## Cursor Cloud specific instructions

### Services overview

| Service | Purpose | How to start |
|---------|---------|-------------|
| MariaDB | Test + dev database | `sudo mysqld_safe &` (wait ~3s) |
| WordPress (dev) | Run plugin in browser | `php -S localhost:8080 -t /tmp/wp-site` |

### Running tests (PHPUnit)

MariaDB must be running. The WP test environment lives in `/tmp/wordpress` (core) and `/tmp/wordpress-tests-lib` (test lib).

```bash
WP_TESTS_DIR=/tmp/wordpress-tests-lib WP_CORE_DIR=/tmp/wordpress vendor/bin/phpunit --verbose
```

### Running lint (PHPCS)

WPCS must be registered with PHPCS. If `vendor/squizlabs/php_codesniffer/CodeSniffer.conf` is missing after a fresh `composer update`, re-register:

```bash
vendor/bin/phpcs --config-set installed_paths vendor/wp-coding-standards/wpcs
```

Then run the CI-equivalent command:

```bash
vendor/bin/phpcs --standard=WordPress -p includes/*.php classes/*.php models/*.php pages/*.php pages/**/*.php
```

### Running WordPress locally

A dev WordPress site is set up at `/tmp/wp-site` with the plugin symlinked from `/workspace`. Admin credentials: `admin` / `admin123`. Start the dev server:

```bash
cd /tmp/wp-site && php -S localhost:8080 -t . &
```

The PHP built-in server does not support `.htaccess` rewrites, so `/go/<slug>/` pretty URLs won't resolve. Use `?surl=<slug>` query-string format instead (e.g. `http://localhost:8080/?surl=test-link`).

### Gotchas

- **CI is now GitHub Actions** (`.github/workflows/ci.yml`), not CircleCI. Some older AGENTS.md/RUNBOOK.md references mention `.circleci/config.yml` which no longer exists.
- **PHP 8+ tests work**: `tests/bootstrap.php` was updated to warn (not exit) on PHP 8+. The CI matrix tests both PHP 7.4 and 8.2.
- **`composer.lock` may be stale**: If `composer install` fails with "lock file not up to date", use `composer update` instead.
- **WPCS path not auto-configured**: After `composer install`/`update`, PHPCS may not find the `WordPress` standard. Run the `--config-set installed_paths` command above.
