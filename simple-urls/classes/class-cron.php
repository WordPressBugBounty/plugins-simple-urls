<?php
/**
 * Declare class Config
 *
 * @package Config
 */

namespace LassoLite\Classes;

use LassoLite\Classes\Estimate_Earning;
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
		Estimate_Earning::CRON_HOOK          => 'weekly',
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
		add_action( Estimate_Earning::CRON_HOOK, array( $this, 'lasso_lite_weekly_estimate_earning' ) );
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

		if ( ! isset( $schedules['weekly'] ) ) {
			$schedules['weekly'] = array(
				'interval' => WEEK_IN_SECONDS,
				'display'  => __( 'Once Weekly' ),
			);
		}

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
			if ( ! is_array( $cron ) ) {
				continue;
			}
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

		$load_slot = self::site_load_slot( self::site_schedule_key() );

		foreach ( $crons as $cron_name => $interval ) {
			$next_scheduled = wp_next_scheduled( $cron_name );
			$is_daily       = ( 'daily' === $interval );
			$is_15m         = ( 'lasso_lite_15_minutes' === $interval );
			$is_weekly      = ( 'weekly' === $interval );

			if ( $next_scheduled && $is_daily && ! self::timestamp_matches_daily_slot( $next_scheduled, $load_slot ) ) {
				wp_clear_scheduled_hook( $cron_name );
				$next_scheduled = false;
			} elseif ( $next_scheduled && $is_15m && ! self::timestamp_matches_15m_slot( $next_scheduled, $load_slot ) ) {
				wp_clear_scheduled_hook( $cron_name );
				$next_scheduled = false;
			} elseif ( $next_scheduled && $is_weekly && ! self::timestamp_matches_daily_slot( $next_scheduled, $load_slot ) ) {
				wp_clear_scheduled_hook( $cron_name );
				$next_scheduled = false;
			}

			if ( ! $next_scheduled ) {
				$current_time  = time();
				$next_run_time = $current_time;
				if ( $is_daily || $is_weekly ) {
					$next_run_time = self::next_daily_run_ts( $load_slot, $current_time );
				} elseif ( $is_15m ) {
					$next_run_time = self::next_15m_run_ts( $load_slot, $current_time );
				}
				wp_schedule_event( $next_run_time, $interval, $cron_name );
			}
		}
	}

	/**
	 * Stable key for load-slot hashing. Prefer site URL.
	 *
	 * @return string
	 */
	public static function site_schedule_key() {
		$url = '';
		if ( function_exists( 'home_url' ) ) {
			$url = (string) home_url();
		}
		if ( function_exists( 'untrailingslashit' ) ) {
			$url = untrailingslashit( strtolower( trim( $url ) ) );
		} else {
			$url = strtolower( trim( $url ) );
		}
		if ( '' !== $url ) {
			return $url;
		}

		return 'lasso-missing-site-key';
	}

	/**
	 * Deterministic minute-of-day slot from a unique site key.
	 *
	 * @param string $site_key home_url.
	 * @return array
	 */
	public static function site_load_slot( $site_key ) {
		$key = (string) $site_key;
		if ( '' === $key ) {
			$key = 'lasso-missing-site-key';
		}

		$minute_of_day = abs( crc32( $key ) ) % 1440;

		return array(
			'minute_of_day' => $minute_of_day,
			'hour'          => (int) floor( $minute_of_day / 60 ),
			'minute'        => $minute_of_day % 60,
		);
	}

	/**
	 * Next daily UTC timestamp at the hashed hour:minute.
	 *
	 * @param array $slot         site_load_slot().
	 * @param int   $current_time Unix timestamp.
	 * @return int
	 */
	public static function next_daily_run_ts( $slot, $current_time ) {
		$hour   = isset( $slot['hour'] ) ? max( 0, min( 23, (int) $slot['hour'] ) ) : 0;
		$minute = isset( $slot['minute'] ) ? max( 0, min( 59, (int) $slot['minute'] ) ) : 0;
		$today  = gmmktime( $hour, $minute, 0 );
		if ( $today > $current_time ) {
			return $today;
		}

		return $today + DAY_IN_SECONDS;
	}

	/**
	 * Next 15-minute UTC timestamp at hashed offset inside the window.
	 *
	 * @param array $slot         site_load_slot().
	 * @param int   $current_time Unix timestamp.
	 * @return int
	 */
	public static function next_15m_run_ts( $slot, $current_time ) {
		$offset     = isset( $slot['minute'] ) ? ( (int) $slot['minute'] % 15 ) : 0;
		$cur_min    = (int) gmdate( 'i', $current_time );
		$hour       = (int) gmdate( 'G', $current_time );
		$window     = $cur_min - ( $cur_min % 15 );
		$candidate  = gmmktime( $hour, $window + $offset, 0 );
		if ( $candidate > $current_time ) {
			return $candidate;
		}

		return $candidate + ( 15 * MINUTE_IN_SECONDS );
	}

	/**
	 * Whether a scheduled timestamp sits on the daily slot.
	 *
	 * @param int   $timestamp Unix timestamp.
	 * @param array $slot      site_load_slot().
	 * @return bool
	 */
	public static function timestamp_matches_daily_slot( $timestamp, $slot ) {
		return (int) gmdate( 'G', $timestamp ) === (int) $slot['hour']
			&& (int) gmdate( 'i', $timestamp ) === (int) $slot['minute'];
	}

	/**
	 * Whether a 15-minute event sits on the hashed offset.
	 *
	 * @param int   $timestamp Unix timestamp.
	 * @param array $slot      site_load_slot().
	 * @return bool
	 */
	public static function timestamp_matches_15m_slot( $timestamp, $slot ) {
		$offset = isset( $slot['minute'] ) ? ( (int) $slot['minute'] % 15 ) : 0;
		return ( (int) gmdate( 'i', $timestamp ) % 15 ) === $offset;
	}

	/**
	 * 0–499ms delay so one site's URL list does not hit lasso.link as one burst.
	 *
	 * @param string $site_key Site URL.
	 * @param string $item_key Request URL.
	 * @return int
	 */
	public static function request_spread_delay_ms( $site_key, $item_key ) {
		return abs( crc32( (string) $site_key . "\0" . (string) $item_key ) ) % 500;
	}

	/**
	 * Whether the current request should spread lasso.link API calls.
	 *
	 * Cron handlers enqueue background-process work that runs via admin-ajax.php
	 * where DOING_CRON is false; LASSO_LITE_BACKGROUND_PROCESS marks those workers.
	 *
	 * @return bool
	 */
	public static function is_paced_background_context() {
		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return true;
		}

		return defined( 'LASSO_LITE_BACKGROUND_PROCESS' ) && LASSO_LITE_BACKGROUND_PROCESS;
	}

	/**
	 * Sleep only on cron/background API calls. Admin save stays instant.
	 *
	 * @param string $item_key      Request URL.
	 * @param bool   $is_lasso_save Interactive save.
	 * @return int Milliseconds slept (0 when skipped).
	 */
	public static function maybe_pace_background_request( $item_key, $is_lasso_save = false ) {
		if ( $is_lasso_save ) {
			return 0;
		}
		if ( ! self::is_paced_background_context() ) {
			return 0;
		}

		$ms = self::request_spread_delay_ms( self::site_schedule_key(), $item_key );
		$ms = (int) apply_filters( 'lasso_lite_request_spread_delay_ms', $ms, $item_key );
		if ( $ms > 0 && $ms < 500 ) {
			usleep( $ms * 1000 );
		}

		return $ms;
	}

	const OPTION_BLS_LAST_TICK = 'lasso_lite_bls_last_tick';

	/**
	 * Per-URL minute-of-day due slot (site + url).
	 *
	 * @param string $site_key Site URL.
	 * @param string $item_key Request URL.
	 * @return int
	 */
	public static function item_due_minute( $site_key, $item_key ) {
		return abs( crc32( (string) $site_key . "\0" . (string) $item_key ) ) % 1440;
	}

	/**
	 * Late = due more than an hour ago (skip the line vs current-hour items).
	 *
	 * @param int $due_minute Minute of day.
	 * @param int $now        Unix timestamp.
	 * @return bool
	 */
	public static function is_late_data_request( $due_minute, $now ) {
		$now_minute = ( (int) gmdate( 'G', $now ) * 60 ) + (int) gmdate( 'i', $now );
		$age        = ( $now_minute - (int) $due_minute + 1440 ) % 1440;
		return $age > 60;
	}

	/**
	 * Hourly due window: current hour plus up to 2h of late catch-up.
	 *
	 * @param string   $item_key    Request URL.
	 * @param int|null $now         Unix timestamp.
	 * @param int|null $last_tick   Previous tick (0 = first run).
	 * @param int|null $due_minute  Injected slot for tests.
	 * @return bool
	 */
	public static function should_send_scheduled_data_request( $item_key, $now = null, $last_tick = null, $due_minute = null ) {
		$now = null === $now ? time() : (int) $now;
		if ( null === $last_tick ) {
			$last_tick = (int) get_option( self::OPTION_BLS_LAST_TICK, 0 );
		}
		if ( $last_tick <= 0 ) {
			$last_tick = $now - HOUR_IN_SECONDS;
		}

		$window_start = max( $last_tick - 300, $now - ( 2 * HOUR_IN_SECONDS ) );
		if ( null === $due_minute ) {
			$due_minute = self::item_due_minute( self::site_schedule_key(), $item_key );
		}

		$midnight  = gmmktime( 0, 0, 0, (int) gmdate( 'n', $now ), (int) gmdate( 'j', $now ), (int) gmdate( 'Y', $now ) );
		$due_today = $midnight + ( (int) $due_minute * 60 );
		if ( $due_today <= $now ) {
			return $due_today > $window_start;
		}

		// Due later today: still catch yesterday's occurrence inside the 2h window
		// (WP-Cron often fires after UTC midnight and would otherwise skip 22:00–23:59).
		return ( $due_today - DAY_IN_SECONDS ) > $window_start;
	}

	/**
	 * Close this hour's window so the next tick only opens new + late-capped work.
	 *
	 * @param int|null $now Unix timestamp.
	 */
	public static function advance_data_request_tick( $now = null ) {
		update_option( self::OPTION_BLS_LAST_TICK, null === $now ? time() : (int) $now, false );
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

	/**
	 * Weekly: refresh cached orphan Affiliate+ payout estimate for the weekly banner.
	 */
	public function lasso_lite_weekly_estimate_earning() {
		Estimate_Earning::fetch_and_cache();
	}
}
