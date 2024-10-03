<?php
/**
 * Declare class License
 *
 * @package License
 */

namespace LassoLite\Classes;

use LassoLite\Classes\Helper as Lasso_Helper;
use LassoLite\Classes\Setting as Lasso_Setting;
use LassoLite\Admin\Constant;

/**
 * Lasso_License
 */
class License {
	/**
	 * Get license of user
	 */
	public static function get_license() {
		return Lasso_Setting::get_setting( 'license_serial', '' );
	}

	/**
	 * Get site id of user
	 */
	public static function get_site_id() {
		return Lasso_Setting::get_setting( 'site_id', '' );
	}

	/**
	 * Save site id of user
	 *
	 * @param string $site_id Site id of website.
	 */
	public static function save_site_id( $site_id ) {
		return Lasso_Setting::set_setting( 'site_id', $site_id );
	}

	/**
	 * Check license of user
	 *
	 * @param string  $license_id License of user.
	 * @param boolean $update_db  Whether licens status is updated in DB. Default to true.
	 */
	public static function check_license( $license_id, $update_db = true ) {
		$headers     = Lasso_Helper::get_headers( $license_id );
		$request_url = Constant::LASSO_LINK . '/license/status';

		$res = Lasso_Helper::send_request( 'get', $request_url, array(), $headers );

		$status_code = $res['status_code'];
		$response    = $res['response'];

		$error_code    = 'other';
		$error_message = 'Error!';
		$status        = false;

		if ( 200 === $status_code ) {
			// ? store user email
			if ( isset( $response->email ) && '' !== $response->email ) {
				update_option( 'lasso_lite_license_email', $response->email );
			}
			if ( isset( $response->end_date ) && $response->end_date > 0 ) {
				update_option( 'lasso_lite_end_date', $response->end_date );
			}

			$status = true;
		} elseif ( 401 === $status_code ) {
			$error_code    = $response->error_code ?? $error_code;
			$error_message = $response->message ?? $error_message;
			$status        = false;

			$is_connected_aff = $response->is_connected_aff ?? 0;
			Helper::update_option( Constant::LASSO_OPTION_IS_CONNECTED_AFFILIATE, $is_connected_aff );
		} else {
			$update_db     = false; // ? Don't update DB if the status is not 200, 401
			$error_message = $res['message'] ?? $error_message;
		}

		// ? store user hash
		if ( isset( $response->hash ) && '' !== $response->hash ) {
			update_option( 'lasso_lite_license_hash', $response->hash );
		}

		// ? update license status in DB
		if ( $update_db ) {
			$status = $status && 1 == $response->is_startup_plan ? 1 : 0; // phpcs:ignore
			update_option( 'lasso_lite_license_status', $status, true );
		}

		return array( Lasso_Helper::cast_to_boolean( $status ), $error_code, $error_message );
	}

	/**
	 * Check license in setting
	 */
	public static function check_user_license() {
		$license = self::get_license();
		if ( empty( $license ) ) {
			return false;
		}

		list($license_status, $error_code, $error_message) = self::check_license( $license );

		return $license_status;
	}

	/**
	 * Get license status in DB
	 */
	public static function get_license_status() {
		$db_status      = get_option( 'lasso_lite_license_status', '' );
		$active_license = Lasso_Helper::cast_to_boolean( $db_status );

		// ? re-activate again if option `lasso_license_status` is not existing
		if ( '' === $db_status ) {
			$active_license = Lasso_Helper::cast_to_boolean( self::check_user_license() );
		}

		return $active_license;
	}

	/**
	 * Send install data to Lasso server
	 */
	public static function lasso_getinfo() {
		global $wp_version;
		global $wpdb;

		// ? Report in
		$data = array(
			'installed_version' => LASSO_LITE_VERSION,
			'datetime'          => gmdate( 'Y-m-d H:i:s' ),
			'site_id'           => self::get_site_id(),
			'install_url'       => site_url(),
			'license_key'       => self::get_license(),
			'wordpress_version' => $wp_version,
			'php_version'       => phpversion(),
			'mysql_version'     => $wpdb->db_version(),
			'is_classic_editor' => Lasso_Helper::is_classic_editor() ? 1 : 0,
		);

		// phpcs:ignore
		$response = Lasso_Helper::send_request( 'post', Constant::LASSO_LINK . '/server/getinfo', $data );

		$site_id = $response['response']->site_id ?? '';

		if ( $site_id ) {
			self::save_site_id( $site_id ); // ? save site_id from DB
		}

		return $response;
	}
}
