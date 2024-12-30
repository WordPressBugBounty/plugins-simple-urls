<?php
/**
 * Plugin Name: Lasso Lite
 * Plugin URI: https://getlasso.co/?utm_source=SimpleURLs&utm_medium=WP
 * Description: Lasso Lite (formerly SimpleURLs) is a complete URL management system that allows you to create, manage, and track outbound links from your site using custom post types and 301 redirects.
 * Author: Lasso
 * Author URI: https://getlasso.co/?utm_source=SimpleURLs&utm_medium=WP
 * Version: 130

 * Text Domain: simple-urls
 * Domain Path: /languages

 * License: GNU General Public License v2.0 (or later)
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 *
 * @package simple-urls
 */

use LassoLite\Admin\Constant;
use LassoLite\Classes\Enum;
use LassoLite\Classes\Helper;
use LassoLite\Classes\License;

// ? ==============================================================================================
// ? WE SHOULD UPDATE THE VERSION NUMBER HERE AS WELL WHEN RELEASING A NEW VERSION
define( 'LASSO_LITE_VERSION', '130' );
// ? ==============================================================================================

function activate_lasso_lite() {
	update_option( Enum::LASSO_LITE_ACTIVE, 1 );
	$license_active = License::get_license_status();
	if ( $license_active === false ) {
		Helper::update_option( Constant::LASSO_OPTION_DISMISS_PROMOTIONS, '0' );
		Helper::update_option( Constant::LASSO_OPTION_AFFILIATE_PROMOTIONS, '1' );
	}
}

function deactivate_lasso_lite() {
	Helper::update_option( Enum::IS_PRE_POPULATED_AMAZON_API, 0 );
}

register_activation_hook( __FILE__, 'activate_lasso_lite' );
register_deactivation_hook( __FILE__, 'deactivate_lasso_lite' );

require_once plugin_dir_path( __FILE__ ) . '/simple-urls.php';
