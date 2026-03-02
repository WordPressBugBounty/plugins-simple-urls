## SKILLS.md

## Why this exists / when to use it

- You want repeatable “recipes” for common work in this repo: where to look, what to edit, how to test.
- You’re new here and need to avoid spelunking through WordPress hooks without a map.
- You’re an AI agent optimizing for **fast correct changes** with evidence-backed entrypoints.
- Each skill below is constrained to **what’s provable from the repo** (paths cited).

---

## Skill 1: Find the real entrypoint(s) and boot chain

- **Where to look**
  - `plugin.php` (`require_once ... '/simple-urls.php';`)
  - `simple-urls.php` (defines `SIMPLE_URLS_SLUG`, loads constants/autoloaders, instantiates `Simple_Urls` and `Init`)
  - `classes/class-init.php` (wires AJAX/hooks/cron/processes)
- **How to do it**
  - Start at `plugin.php`, follow the `require_once` to `simple-urls.php`.
  - From `simple-urls.php`, note what is loaded unconditionally vs admin-only.
  - Use `classes/class-init.php` arrays to see what “new UI” components exist.
- **How to test it**
  - PHPUnit will load the plugin via `tests/bootstrap.php` which requires `simple-urls.php`.
  - Evidence: `tests/bootstrap.php` `_manually_load_plugin()` → `require ... '/simple-urls.php';`

---

## Skill 2: Change cloaked redirect behavior (the `surl` link)

- **Where to look**
  - `includes/class-simple-urls.php`:
    - hooks: `add_action('template_redirect', ... 'count_and_redirect')`
    - redirect meta key: `_surl_redirect`
    - counter meta key: `_surl_count`
    - filter/action: `simple_urls_redirect_url`, `simple_urls_redirect`
  - `pages/class-hook.php`:
    - `add_filter( 'simple_urls_redirect_url', ... 'lasso_lite_redirect' ...)` (Amazon rewrite)
- **How to do it**
  - Prefer adding behavior behind the existing filter `simple_urls_redirect_url` rather than editing `count_and_redirect()` directly (keeps public API stable).
  - If you must change core redirect, update tests under `tests/LassoSimpleURLsTest.php`.
- **How to test it**
  - Unit test entrypoint: `tests/LassoSimpleURLsTest.php` (covers `Simple_Urls`).

---

## Skill 3: Add or modify a `[lasso]` shortcode behavior

- **Where to look**
  - Registration: `pages/class-hook.php` → `add_shortcode( 'lasso', ... )`
  - Implementation: `classes/class-shortcode.php` → `lasso_lite_core_shortcode($attr)`
  - Display template: `admin/views/displays/single.php` (rendered via `Helper::include_with_variables(...)`)
  - Tests: `tests/classes/LassoLiteShortCodeTest.php`
- **How to do it**
  - Keep `id` as the required attribute unless you also update call sites/tests (code returns `false` without it).
    - Evidence: `classes/class-shortcode.php` says “must be having `id` parameter” then `if ( ! $post_id ) { return false; }`
  - For markup changes, update expected strings in `tests/classes/LassoLiteShortCodeTest.php`.
- **How to test it**
  - Run PHPUnit; the shortcode tests assert container markup is present.
    - Evidence: `tests/classes/LassoLiteShortCodeTest.php` checks for `<div id="lasso-lite-anchor-id-...">`.

---

## Skill 4: Add an admin-ajax endpoint (securely)

- **Where to look**
  - Endpoint registration patterns:
    - `pages/class-ajax.php` (general)
    - `pages/dashboard/class-ajax.php`
    - `pages/settings/class-ajax.php`
    - `pages/groups/class-ajax.php`
    - `pages/import-urls/class-ajax.php`
    - `pages/url-details/class-ajax.php`
  - Security gate: `classes/class-helper.php` → `verify_access_and_nonce(...)`
  - Nonce wiring into JS: `pages/class-hook.php` → `optionsNonce => wp_create_nonce( Constant::LASSO_LITE_NONCE . wp_salt() )`
  - Nonce constant name: `admin/constant.php` → `Constant::LASSO_LITE_NONCE`
- **How to do it**
  - Add a `add_action( 'wp_ajax_your_action', ... )` in the relevant `pages/**/class-ajax.php`.
  - Start the handler with `Helper::verify_access_and_nonce(...)` (choose `$allow_edit_post_access` only if editors/authors/contributors should be allowed).
  - Return via `wp_send_json_success(...)` / `wp_send_json_error(...)` for consistent client behavior.
- **How to test it**
  - **Unit tests**: can directly call your handler method if it doesn’t hard-exit; otherwise refactor logic into a pure method and unit-test that.
  - **Manual smoke**:
    - Open an admin page where scripts load (see `pages/class-hook.php` `add_scripts()`), then in the browser console read `lassoLiteOptionsData.optionsNonce` and call `admin-ajax.php` with `action=...&nonce=...`.

---

## Skill 5: Add/modify settings (defaults + persistence)

- **Where to look**
  - Defaults: `admin/constant.php` → `Constant::DEFAULT_SETTINGS`
  - Storage: `classes/class-setting.php` uses `get_option('lassolite_settings')` and `update_option('lassolite_settings', ...)`
  - Settings AJAX: `pages/settings/class-ajax.php` (saves settings)
- **How to do it**
  - Add a new default key in `Constant::DEFAULT_SETTINGS`.
  - Read/write via `Setting::get_setting(...)`, `Setting::set_setting(...)`, or `Setting::set_settings(...)`.
  - If a UI control exists, trace its handler in `pages/settings/class-ajax.php` and related `admin/views/settings/*.php`.
- **How to test it**
  - Add a unit test that calls `Setting::set_setting(...)` then asserts `Setting::get_setting(...)` returns the value.
  - Evidence for mechanism: `classes/class-setting.php` `set_setting()` merges into `lassolite_settings`.

---

## Skill 6: Add/modify DB tables (schema + migrations)

- **Where to look**
  - Orchestration/versioning: `classes/class-update-db.php` (gates by `lasso_version`, calls `create_tables()`)
  - Base model + `dbDelta`: `models/class-model.php` (uses `dbDelta( $sql )`, see grep)
  - Existing tables as examples:
    - `models/class-amazon-products.php`
    - `models/class-url-details.php`
    - `models/class-revert.php`
  - DB query layer: `classes/class-lasso-db.php`
- **How to do it**
  - Create a new model under `models/` with `create_table()` returning `CREATE TABLE ...` + charset/collate.
  - Wire it into `Update_DB::create_tables()` and/or `update_lasso_database()` with a version gate.
  - Avoid breaking existing tables; add columns with backward compatible defaults.
- **How to test it**
  - Add a unit test that instantiates the model and calls `create_table()` (requires WP test DB).
  - Use CI-like WP test DB setup (see `.circleci/config.yml` and `.travis.yml`).

---

## Skill 7: Add/modify scheduled work (cron)

- **Where to look**
  - Cron schedules + hooks: `classes/class-cron.php`
  - Process instantiation: `classes/class-init.php` (`$this->classes` includes `\LassoLite\Classes\Cron`)
- **How to do it**
  - Add a hook name to `Cron::CRONS` and a corresponding `add_action( 'your_hook', ... )` in the constructor.
  - Gate the job with settings/options where needed (pattern: `Setting::get_settings()` then check a flag).
    - Evidence: `classes/class-cron.php` checks `$settings['amazon_pricing_daily']` before running Amazon jobs.
- **How to test it**
  - Unit test the handler method directly (don’t rely on WP cron actually firing in PHPUnit).

---

## Skill 8: Run unit tests locally (mirroring CI)

- **Where to look**
  - PHPUnit config: `phpunit.xml.dist` (bootstrap, test discovery)
  - Test bootstrap: `tests/bootstrap.php` (loads plugin, expects WP test lib)
  - CI truth: `.circleci/config.yml` (`unit_test`) and legacy `.travis.yml`
- **How to do it**
  - Install PHP deps: `composer install`
    - Evidence: `.circleci/config.yml` runs `composer install` before tests.
  - Provision the WP test DB and run PHPUnit like CircleCI does:
    - Evidence: `.circleci/config.yml` runs `bash bin/install-wp-tests.sh ...` then `phpunit ...`
  - Avoid PHP 8+ for tests:
    - Evidence: `tests/bootstrap.php` exits when `PHP_MAJOR_VERSION >= 8`.
- **How to test it**
  - You’re done when `phpunit` matches CI behavior (see `.circleci/config.yml`).

---

## Skill 9: Run PHPCS (WordPress standard) locally

- **Where to look**
  - CI PHPCS: `.circleci/config.yml` `wpcs` step (runs `vendor/bin/phpcs --standard=WordPress ...`)
  - Composer script: `composer.json` `scripts.phpcs` (narrower scope)
- **How to do it**
  - Run the CI-equivalent command:
    - `vendor/bin/phpcs --standard=WordPress -p -v includes/ classes/ models/ pages/`
  - Or run the repo’s composer script:
    - `composer run phpcs`
- **How to test it**
  - CI should pass the PHPCS job (same standard/scope).

---

## Skill 10: Update translations (POT + textdomain)

- **Where to look**
  - NPM scripts: `package.json` (`makepot`, `addtextdomain`)
  - POT file: `languages/simple-urls.pot`
  - Textdomain load: `includes/class-simple-urls.php` → `load_plugin_textdomain('simple-urls', ...)`
- **How to do it**
  - Install Node deps: `npm ci` (or `npm install`) then:
    - `npm run addtextdomain`
    - `npm run makepot`
- **How to test it**
  - Confirm `languages/simple-urls.pot` changes as expected.
  - Confirm no PHP warnings from `load_plugin_textdomain(...)` path.

---

## Skill 11: Understand and edit deploy/release automation (without guessing)

- **Where to look**
  - WordPress.org deploy on release publish: `.github/workflows/main.yml`
  - RC zip + S3 upload on push to master: `.github/workflows/release-rc.yml`
- **How to do it**
  - For WP.org deploy:
    - The workflow uses `10up/action-wordpress-plugin-deploy@stable` and requires secrets `SVN_USERNAME` / `SVN_PASSWORD`.
      - Evidence: `.github/workflows/main.yml` has env `SVN_USERNAME: ${{ secrets.SVN_USERNAME }}`.
  - For RC zip:
    - The workflow minifies CSS via `python minify_css.py` and zips after deleting dev-only files.
      - Evidence: `.github/workflows/release-rc.yml` “Remove unwanted files” includes `rm -rf ... tests ... .github ... composer.json ...`
    - It uploads to S3 buckets using secrets `S3_BUCKET_LASSO_LITE_RC`, `S3_BUCKET_LASSO_LITE_PUBLIC`, `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`.
- **How to test it**
  - Confirm workflow YAML changes are syntactically valid.
  - Dry-run locally is **Unknown** (no local tooling is encoded in the repo); validate via GitHub Actions on a branch.

