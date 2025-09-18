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
	 * Create hook for the new cron
	 */
	public function lasso_create_schedule_hook() {
		$crons       = self::CRONS;
		$events      = array();
		$crons_array = _get_cron_array();

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
}
