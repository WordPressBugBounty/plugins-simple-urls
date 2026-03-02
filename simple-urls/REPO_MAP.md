## REPO_MAP.md

## Why this exists / when to use it

- You need to understand “what is this system?” in under 10 minutes.
- You’re trying to find the correct entrypoint for a change (redirect behavior, admin UI, shortcode rendering, AJAX action, cron/job, DB table).
- You want a **map of the repo** that’s accurate and evidence-backed, not a guessing game.
- You’re an AI agent that must navigate by **file paths + concrete hooks**, not vibes.

## System overview (what this plugin is)

This repository is a **WordPress plugin** called “Lasso Lite (formerly SimpleURLs / Simple URLs)” that:

- Registers a custom post type (`surl`) whose single view acts like a **cloaked redirect** with click counting.
  - Evidence: `includes/class-simple-urls.php` registers post type `surl` and in `count_and_redirect()` updates `_surl_count` and redirects to `_surl_redirect`.
- Adds an admin experience (dashboard/settings/import/groups) and a `[lasso]` shortcode to render “display boxes”.
  - Evidence:
    - `pages/class-hook.php` registers shortcode `[lasso]`.
    - `classes/class-shortcode.php` renders via `admin/views/displays/single.php`.
- Maintains a small set of custom DB tables for Amazon product data and link details (in addition to WordPress posts/postmeta).
  - Evidence:
    - `models/class-amazon-products.php` table `lasso_lite_amazon_products`
    - `models/class-url-details.php` table `lasso_lite_url_details`
    - `models/class-revert.php` table `lasso_lite_revert`
    - `classes/class-update-db.php` orchestrates table creation/version gates.

## Boot/entrypoints (the chain of responsibility)

### 1) WordPress loads `plugin.php`

- Defines plugin metadata + version, and includes the main bootstrap.
  - Evidence: `plugin.php` contains:
    - `define( 'LASSO_LITE_VERSION', '132' );`
    - `require_once plugin_dir_path( __FILE__ ) . '/simple-urls.php';`

### 2) `simple-urls.php` sets up globals + loads modules

- Defines core constants and loads:
  - `admin/constant.php` (plugin constants/defaults)
  - `autoload.php` (autoloading for `LassoLite\...`)
  - `vendor-prefix/vendor/autoload.php` (Composer autoload, vendored deps)
  - Sentry integration (file path in repo; see integration points below)
  - `includes/class-simple-urls.php` (legacy CPT/redirect)
  - `includes/class-simple-urls-admin.php` (legacy metabox/list columns) in admin only
  - `new Init()` (modern init wiring for pages/ajax/hooks/processes)
  - Evidence: `simple-urls.php` contains `require_once SIMPLE_URLS_DIR . '/admin/constant.php'; ... new Simple_Urls(); ... new Init();`

### 3) `classes/class-init.php` wires “new UI” modules

`Init` constructs and loads:

- AJAX registrars: `\LassoLite\Pages\Ajax`, `\LassoLite\Pages\Dashboard\Ajax`, etc.
- Hook registrars: `\LassoLite\Pages\Hook`, `\LassoLite\Pages\Url_Details\Hook`, `\LassoLite\Pages\Import_Urls\Hook`
- Cron: `\LassoLite\Classes\Cron`
- Background processes: `\LassoLite\Classes\Processes\Amazon`, `Import_All`, `Revert_All`, etc.

Evidence: `classes/class-init.php` arrays `$this->ajaxes`, `$this->hooks`, `$this->classes`, `$this->processes` and `load_classes()`.

## Core behaviors & where to change them

### Cloaked redirect + click count (CPT single)

- **CPT registration + rewrite slug**: `includes/class-simple-urls.php` → `register_post_type()`
  - Evidence: sets `$rewrite_slug_default = 'go'` and registers `'rewrite' => ['slug' => $rewrite_slug, ...]`.
- **Redirect + count**: `includes/class-simple-urls.php` → `count_and_redirect()`
  - Evidence: reads `get_post_meta( ... '_surl_redirect' ...)`, increments `_surl_count`, then `wp_redirect( ..., 301 )`.
- **Extension points**:
  - Filter redirect URL: `apply_filters( 'simple_urls_redirect_url', $redirect, $count )`
  - Action before redirect: `do_action( 'simple_urls_redirect', $redirect, $count )`
  - Evidence: both appear in `includes/class-simple-urls.php`.

### Admin UI pages + assets + nonce wiring

- **Admin menu + editor integrations**: `pages/class-hook.php`
  - Evidence: `register_hooks()` adds `admin_menu`, `admin_enqueue_scripts`, `enqueue_block_editor_assets`, TinyMCE filters, Elementor hook, etc.
- **AJAX endpoints**: `pages/**/class-ajax.php`
  - Evidence: `pages/class-ajax.php` registers actions like `wp_ajax_lasso_lite_add_a_new_link`.
- **Nonce + AJAX URL passed to JS**: `pages/class-hook.php` → `add_scripts()`
  - Evidence: `optionsNonce => wp_create_nonce( Constant::LASSO_LITE_NONCE . wp_salt() )` and `ajax_url => admin_url('admin-ajax.php')`.
- **Security gate**: `classes/class-helper.php` → `verify_access_and_nonce()`
  - Evidence: checks capability + `wp_verify_nonce( $nonce, Constant::LASSO_LITE_NONCE . wp_salt() )`.

### Shortcode rendering

- **Registration**: `pages/class-hook.php` registers `[lasso]`.
  - Evidence: `add_shortcode( 'lasso', array( $lasso_shortcode, 'lasso_lite_core_shortcode' ) );`
- **Implementation**: `classes/class-shortcode.php` → `lasso_lite_core_shortcode($attr)`
  - Evidence: requires `id`, checks post type `surl`, then includes `admin/views/displays/single.php`.
- **Contract smoke test**: `tests/classes/LassoLiteShortCodeTest.php`
  - Evidence: asserts output contains `<div id="lasso-lite-anchor-id-...">`.

### Cron/jobs

- **Schedules + handlers**: `classes/class-cron.php`
  - Evidence: defines a custom schedule `lasso_lite_15_minutes` and schedules hooks like `lasso_lite_update_amazon`.

### Custom DB tables / migrations

- **Orchestrator**: `classes/class-update-db.php` gates schema updates by `lasso_version` option and calls `create_table()`.
  - Evidence: `update_lasso_database()` checks numeric thresholds and calls model `create_table()`.
- **Tables**:
  - `models/class-amazon-products.php` → `lasso_lite_amazon_products`
  - `models/class-url-details.php` → `lasso_lite_url_details`
  - `models/class-revert.php` → `lasso_lite_revert`
- **DB util layer**: `classes/class-lasso-db.php` (higher-level queries across WP tables + plugin tables)

## External integrations (what calls out of WordPress)

### Lasso API (license/support/etc.)

- Base URL: `Constant::LASSO_LINK` (value in code; **treat as config**).
  - Evidence: `admin/constant.php` defines `const LASSO_LINK = ...`.
- License status: `classes/class-license.php` calls `${LASSO_LINK}/license/status`.
  - Evidence: `classes/class-license.php` builds `$request_url = Constant::LASSO_LINK . '/license/status'` and sends via `Helper::send_request(...)`.
- Support enable: `classes/class-setting.php` posts JWT payload to `${LASSO_LINK}/lasso-lite/enable-support`.
  - Evidence: `classes/class-setting.php` uses `JWT::encode(..., Constant::JWT_SECRET_KEY, 'HS256')` then `Helper::send_request('post', Constant::LASSO_LINK . '/lasso-lite/enable-support', ...)`.
- HTTP transport: `classes/class-helper.php` uses `wp_remote_get/post/request`.
  - Evidence: `classes/class-helper.php` → `send_request()`.

### Sentry

- Integration is included during bootstrap.
  - Evidence: `simple-urls.php` requires `libs/lasso-lite/lasso-lite-sentry.php` (file exists in repo; direct read may be restricted, but grep confirms usage of `Constant::SENTRY_DSN`).
- DSN is also wired into admin UI JS.
  - Evidence: `admin/views/footer.php` includes `dsn: '<?php echo Constant::SENTRY_DSN; ... ?>'`.

## Folder map (top-level)

- `plugin.php`: WordPress plugin header + boot include.
- `simple-urls.php`: bootstrap constants + requires + instantiation.
- `includes/`: legacy `Simple_Urls` (CPT/redirect) and `Simple_Urls_Admin` (metabox/columns).
- `classes/`: modern PHP logic (settings, helpers, DB, cron, processes, etc).
- `pages/`: admin page wiring: hooks/menus/assets and AJAX endpoints, grouped by page area.
- `models/`: DB table models (create_table, queries via shared base `Model`).
- `admin/`: UI views (`admin/views/**`) and bundled static assets (`admin/assets/**`).
- `tests/`: PHPUnit tests + bootstrap.
- `vendor-prefix/`: vendored Composer deps (checked into repo).
- `.github/workflows/`: deploy/packaging automation (WordPress.org deploy + RC zip/S3 upload).
  - Evidence: only `.github/workflows/main.yml` and `.github/workflows/release-rc.yml` exist.
- `.circleci/config.yml`: CI (PHPCS + unit tests).
- `.travis.yml`: legacy CI config (PHPUnit + PHPCS).

## Start here (high-leverage files)

1. `plugin.php` (entrypoint, version)
2. `simple-urls.php` (bootstrap and module wiring)
3. `includes/class-simple-urls.php` (CPT + redirect + click count)
4. `pages/class-hook.php` (admin hooks, menu, nonce, shortcode registration, editor integrations)
5. `pages/**/class-ajax.php` (admin-ajax endpoints)
6. `classes/class-helper.php` (HTTP, nonce/capability gate, misc utilities)
7. `classes/class-setting.php` + `admin/constant.php` (settings defaults + storage + constants)
8. `classes/class-update-db.php` + `models/*` (custom tables + migrations)
9. `classes/class-cron.php` (scheduled jobs)
10. `.circleci/config.yml` (truth for lint + test commands)
11. `.travis.yml` (legacy CI matrix)

