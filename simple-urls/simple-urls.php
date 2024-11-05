<?php

use LassoLite\Classes\Init;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
define( "SIMPLE_URLS_SLUG", "surl" );
define( 'SIMPLE_URLS_DIR', plugin_dir_path( __FILE__ ) );
define( 'SIMPLE_URLS_URL', plugins_url( '', __FILE__ ) );
define( 'SIMPLE_URLS_PLUGIN_PATH', __DIR__ );

require_once SIMPLE_URLS_DIR . '/admin/constant.php';
require_once SIMPLE_URLS_DIR . DIRECTORY_SEPARATOR . 'autoload.php';
require_once SIMPLE_URLS_DIR . '/vendor-prefix/vendor/autoload.php';

// ? Sentry declaration
require_once SIMPLE_URLS_DIR . '/libs/lasso-lite/lasso-lite-sentry.php';

require_once SIMPLE_URLS_DIR . '/includes/class-simple-urls.php';
new Simple_Urls();

if ( is_admin() ) {
	require_once SIMPLE_URLS_DIR . '/includes/class-simple-urls-admin.php';
	new Simple_Urls_Admin();
}

new Init();






