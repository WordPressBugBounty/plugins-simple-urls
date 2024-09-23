<?php
/**
 * LassoLite Install - Ajax.
 *
 * @package Pages
 */

namespace LassoLite\Pages\Install;

use LassoLite\Classes\Setting;
use LassoLite\Classes\License;

/**
 * Lasso Install - Ajax.
 */
class Ajax {
	const ACTIVATE_FIRST_TIME_KEY = 'lasso_activate_first_time';

	/**
	 * Declare "Lasso ajax requests" to WordPress.
	 */
	public function register_hooks() {
		add_action( 'wp_ajax_lasso_lite_activate_license', array( $this, 'lasso_lite_activate_license' ) );
	}

	/**
	 * Activate lasso plugin
	 */
	public function lasso_lite_activate_license() {
		$license       = wp_unslash( $_POST['license'] ?? '' ); // phpcs:ignore

		$setting = new Setting();

		Setting::set_setting( 'license_serial', $license );
		License::lasso_getinfo();

		list($license_status, $error_code, $error_message) = License::check_license( $license );
		if ( $license_status ) {

			// ? Set flag activate first time
			update_option( self::ACTIVATE_FIRST_TIME_KEY, 1 );
		}

		wp_send_json_success(
			array(
				'status'        => $license_status,
				'error_code'    => $error_code,
				'error_message' => $error_message,
				'redirect_url'  => $setting->get_dashboard_page(),
				'hash'          => get_option( 'License_hash', '' ),
			)
		);
	} // @codeCoverageIgnore
}
