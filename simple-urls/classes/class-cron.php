<?php
/**
 * Declare class Config
 *
 * @package Config
 */

namespace LassoLite\Classes;

use LassoLite\Classes\License;
use LassoLite\Classes\Processes\Amazon;
use LassoLite\Classes\Processes\Amazon_Shortlink;
use LassoLite\Classes\Processes\Import_All;
use LassoLite\Classes\Processes\Revert_All;
use LassoLite\Classes\Setting;
use LassoLite\Classes\Enum;
use LassoLite\Admin\Constant;

/**
 * Config
 */
class Cron {

	const CRONS = array(
		'lasso_lite_amazon_shortlink'        => 'lasso_lite_15_minutes',
		'lasso_lite_update_amazon'           => 'lasso_lite_15_minutes',
		'lasso_lite_import_all'              => 'lasso_lite_15_minutes',
		'lasso_lite_revert_all'              => 'lasso_lite_15_minutes',
		'lasso_lite_tracking_support_status' => 'daily',
		'lasso_lite_update_license_status'   => 'daily',
		'lasso_lite_cron_get_snippet'        => 'daily',
		'lasso_lite_cron_get_js_domain'      => 'daily',
		'lasso_lite_cron_get_info'           => 'daily',
		'lasso_lite_check_lite_user'         => 'daily',
	);

	/**
	 * Cron constructor.
	 */
	public function register_hooks() {
		add_filter( 'cron_schedules', array( $this, 'add_lasso_cron' ) );
		add_action( 'lasso_lite_tracking_support_status', array( $this, 'lasso_lite_tracking_support_status' ) );
		add_action( 'lasso_lite_import_all', array( $this, 'lasso_import_all' ) );
		add_action( 'lasso_lite_revert_all', array( $this, 'lasso_revert_all' ) );
		add_action( 'lasso_lite_update_amazon', array( $this, 'lasso_lite_update_amazon' ) );
		add_action( 'lasso_lite_amazon_shortlink', array( $this, 'lasso_lite_amazon_shortlink' ) );
		add_action( 'lasso_lite_update_license_status', array( $this, 'lasso_lite_update_license_status' ) );
		add_action( 'lasso_lite_cron_get_snippet', array( $this, 'lasso_lite_cron_get_snippet' ) );
		add_action( 'lasso_lite_cron_get_js_domain', array( $this, 'lasso_lite_cron_get_js_domain' ) );
		add_action( 'lasso_lite_cron_get_info', array( $this, 'lasso_lite_cron_get_info' ) );
		add_action( 'lasso_lite_check_lite_user', array( $this, 'lasso_lite_check_lite_user' ) );
		$this->lasso_create_schedule_hook();
	}

	/**
	 * Add a custom cron to WordPress
	 *
	 * @param array $schedules An array of non-default cron schedules. Default empty.
	 */
	public function add_lasso_cron( $schedules ) {
		$schedules['lasso_lite_15_minutes'] = array(
			'interval' => 15 * MINUTE_IN_SECONDS, // ? 15 minutes in seconds
			'display'  => __( '15 minutes' ),
		);

		return $schedules;
	}

	/**
	 * Upgrade the stored cron option structure to version 2 (hash-keyed args).
	 *
	 * Mirrors WordPress core cron array shape so iteration in this class matches
	 * `wp_next_scheduled` / `wp_schedule_event` expectations.
	 *
	 * @param array $cron Cron info array from lasso_get_stored_cron_array().
	 * @return array Upgraded cron info array.
	 */
	private function lasso_upgrade_stored_cron_array( $cron ) {
		if ( isset( $cron['version'] ) && 2 === $cron['version'] ) {
			return $cron;
		}

		$new_cron = array();

		foreach ( (array) $cron as $timestamp => $hooks ) {
			foreach ( (array) $hooks as $hook => $args ) {
				$key = md5( serialize( $args['args'] ) );

				$new_cron[ $timestamp ][ $hook ][ $key ] = $args;
			}
		}

		$new_cron['version'] = 2;

		update_option( 'cron', $new_cron );

		return $new_cron;
	}

	/**
	 * Load the `cron` option as an array, upgrading legacy shape when needed.
	 *
	 * @return array Cron events (no `version` key in the returned array).
	 */
	private function lasso_get_stored_cron_array() {
		$cron = get_option( 'cron' );
		if ( ! is_array( $cron ) ) {
			return array();
		}

		if ( ! isset( $cron['version'] ) ) {
			$cron = $this->lasso_upgrade_stored_cron_array( $cron );
		}

		unset( $cron['version'] );

		return $cron;
	}

	/**
	 * Create hook for the new cron
	 */
	public function lasso_create_schedule_hook() {
		$crons       = self::CRONS;
		$events      = array();
		$crons_array = $this->lasso_get_stored_cron_array();

		if ( ! is_array( $crons_array ) ) {
			return;
		}

		foreach ( $crons_array as $time => $cron ) {
			foreach ( $cron as $hook => $dings ) {
				if ( strpos( $hook, 'lasso_lite_' ) === false ) {
					continue;
				}

				foreach ( $dings as $sig => $data ) {
					$interval = $data['interval'] ?? HOUR_IN_SECONDS;

					// ? get the cron that is less than the existing one
					if ( isset( $events[ $hook ] ) && $interval >= $events[ $hook ]->interval ) {
						continue;
					}

					$events[ $hook ] = (object) array(
						'hook'     => $hook,
						'time'     => $time, // ? UTC
						'schedule' => $data['schedule'],
						'interval' => $interval,
					);
				}
			}
		}

		foreach ( $crons as $cron_name => $interval ) {
			$next_scheduled = wp_next_scheduled( $cron_name );
			if ( ! $next_scheduled ) {
				// No schedule exists - create a new one.
				wp_schedule_event( time(), $interval, $cron_name );
			}
		}
	}

	/**
	 * Tracking support status
	 */
	public function lasso_lite_tracking_support_status() {
		$settings = Setting::get_settings();
		if ( boolval( $settings[ Enum::SUPPORT_ENABLED ] ) ) {
			Setting::save_support( false );
		}
	}

	/**
	 * Import all
	 */
	public function lasso_import_all() {
		$allow_import_all = get_option( Import_All::OPTION, '0' );
		if ( 1 === intval( $allow_import_all ) ) {
			$lasso_import_all = new Import_All();
			$lasso_import_all->import();
		}
	}

	/**
	 * Revert all
	 */
	public function lasso_revert_all() {
		$allow_revert_all = get_option( Revert_All::OPTION, '0' );
		if ( 1 === intval( $allow_revert_all ) ) {
			$lasso_import_all = new Revert_All();
			$lasso_import_all->revert();
		}
	}

	/**
	 * Revert all
	 */
	public function lasso_lite_update_amazon() {
		$settings = Setting::get_settings();
		if ( boolval( $settings['amazon_pricing_daily'] ) ) {
			$lasso_amazon = new Amazon();
			$lasso_amazon->run();
		}
	}

	/**
	 * Revert all
	 */
	public function lasso_lite_amazon_shortlink() {
		$settings = Setting::get_settings();
		if ( boolval( $settings['amazon_pricing_daily'] ) ) {
			$lasso_amazon = new Amazon_Shortlink();
			$lasso_amazon->run();
		}
	}

	/**
	 * Update license status.
	 */
	public function lasso_lite_update_license_status() {
		License::check_user_license();
	}

	/**
	 * Daily update snippet: Fetch snippet performance and write to connect-snippet.min.js
	 */
	public function lasso_lite_cron_get_snippet() {
		try {
			$url     = Constant::LASSO_LINK . '/api/snippet/performance?ver=' . time();
			$res     = Helper::send_request( 'get', $url );

			$status_code = isset( $res['status_code'] ) ? intval( $res['status_code'] ) : 0;
			$body        = isset( $res['response'] ) ? ( $res['response']->content ?? '' ) : '';
			$body_str    = is_string( $body ) ? $body : wp_json_encode( $body );

			if ( 200 === $status_code && ! empty( $body_str ) && strpos( $body_str, 'LASSO_REDIRECT_AMAZON_URL,' ) !== false ) {
				$file_path = LASSO_CONNECT_SNIPPET_FILE_LITE;
				$result    = file_put_contents( $file_path, (string) $body_str );
				if ( false === $result ) {
					return false;
				}
			}
			return true;
		} catch ( \Exception $e ) {
			return false;
		}
	}

	/**
	 * Fetches the JS domain URL from the remote API and updates the 'js_domain' option if valid.
	 *
	 * @return string Returns an empty string. Returns early on exception or after processing.
	 * @throws \Exception This method catches all exceptions internally and does not throw.
	 */
	public function lasso_lite_cron_get_js_domain() {
		try {
			$url     = Constant::LASSO_LINK . '/api/js-domain?ver=' . time();
			$res     = Helper::send_request( 'get', $url );
			$status_code = intval( $res['status_code'] ?? 500 );
			$response    = $res['response'] ?? '';

			if ( 200 === $status_code && $response ) {
				// API returns JSON object that contains a URL to the JS file
				$file_url = $response->url ?? '';
				// Only return the URL. Do not fetch or write files here.
				if ( ! empty( $file_url ) && Helper::validate_url( $file_url ) ) {
					Helper::update_option( 'js_domain', $file_url );
				}

				$full_file_url = $response->full_url ?? '';
				if ( ! empty( $full_file_url ) && Helper::validate_url( $full_file_url ) ) {
					Helper::update_option( 'full_js_domain', $full_file_url );
				}
			}
		} catch ( \Exception $e ) {
			return '';
		}

		return '';
	}

	/**
	 * Get info
	 */
	public function lasso_lite_cron_get_info() {
		try {
			License::lasso_getinfo(['license_key']);
		} catch ( \Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Daily: request lite user record for this site's admin email.
	 *
	 * @return bool True when the HTTP request completes with 200.
	 */
	public function lasso_lite_check_lite_user() {
		try {
			$admin_email = get_option( 'admin_email' );
			if ( empty( $admin_email ) || ! is_email( $admin_email ) ) {
				return false;
			}

			$url     = Constant::LASSO_LINK . '/plugin/lite/users/' . rawurlencode( $admin_email );
			$headers = Helper::get_headers();
			$res     = Helper::send_request( 'get', $url, array(), $headers );

			$status_code = isset( $res['status_code'] ) ? intval( $res['status_code'] ) : 0;
			return 200 === $status_code;
		} catch ( \Exception $e ) {
			return false;
		}
	}
}
