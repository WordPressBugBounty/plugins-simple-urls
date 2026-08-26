<?php
/**
 * Plugin Name: Lasso Lite
 * Plugin URI: https://getlasso.co/?utm_source=lasso-lite&utm_medium=wp&utm_campaign=plugin-header
 * Description: Stop pasting long affiliate URLs into every post. Cloak your links, add product displays, and track clicks in WordPress.
 * Author: Lasso
 * Author URI: https://getlasso.co/?utm_source=lasso-lite&utm_medium=wp&utm_campaign=plugin-header
 * Version: 155

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
use LassoLite\Pages\Hook;

// ? ==============================================================================================
// ? WE SHOULD UPDATE THE VERSION NUMBER HERE AS WELL WHEN RELEASING A NEW VERSION
define( 'LASSO_LITE_VERSION', '155' );
// ? ==============================================================================================

function activate_lasso_lite() {
	update_option( Enum::LASSO_LITE_ACTIVE, 1 );
	$license_active = License::get_license_status();
	if ( $license_active === false ) {
		// Fresh installs show the promo banner; preserve a prior permanent dismiss on re-activation.
		if ( false === Helper::get_option( Constant::LASSO_OPTION_DISMISS_PROMOTIONS, false ) ) {
			Helper::update_option( Constant::LASSO_OPTION_DISMISS_PROMOTIONS, '0' );
		}
		// Footer affiliate promo bar uses affiliate_promotions=1 (show); preserve user dismiss (=0).
		if ( false === Helper::get_option( Constant::LASSO_OPTION_AFFILIATE_PROMOTIONS, false ) ) {
			Helper::update_option( Constant::LASSO_OPTION_AFFILIATE_PROMOTIONS, '1' );
		}
	}
	Hook::lasso_register_connect_snippet_rewrite();
	flush_rewrite_rules();
}

function deactivate_lasso_lite() {
	Helper::update_option( Enum::IS_PRE_POPULATED_AMAZON_API, 0 );
	flush_rewrite_rules();
}

register_activation_hook( __FILE__, 'activate_lasso_lite' );
register_deactivation_hook( __FILE__, 'deactivate_lasso_lite' );

require_once plugin_dir_path( __FILE__ ) . '/simple-urls.php';
