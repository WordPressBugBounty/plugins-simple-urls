<?php
/**
 * Orphan-site estimated Affiliate+ payout (POST /api/estimate-earning).
 *
 * Weekly WP-Cron refreshes a transient cache; admin UI reads the cache only.
 *
 * @package Estimate_Earning
 */

namespace LassoLite\Classes;

use LassoLite\Admin\Constant;

/**
 * Estimate_Earning
 */
class Estimate_Earning {

	const TRANSIENT_KEY = 'lasso_lite_estimate_earning';

	/** Weekly cron + buffer (do not shorten below 8 days). */
	const CACHE_TTL = WEEK_IN_SECONDS + DAY_IN_SECONDS;

	const CRON_HOOK = 'lasso_lite_weekly_estimate_earning';

	/**
	 * Whether the site has a linked Lasso app account (skip fetch + banner).
	 *
	 * Uses `surl_lasso_account_user_id` only; email alone does not count as linked.
	 *
	 * @return bool
	 */
	public static function has_linked_lasso_account() {
		$user_id = Helper::get_option( Constant::LASSO_ACCOUNT_USER_ID, 0 );
		if ( is_string( $user_id ) && '' === trim( $user_id ) ) {
			return false;
		}

		return (int) $user_id > 0;
	}

	/**
	 * Step 4 (#442): show weekly earnings banner for orphan sites only.
	 *
	 * @return bool
	 */
	public static function should_show_orphan_earnings_banner() {
		return ! self::has_linked_lasso_account();
	}

	/**
	 * Human-readable reason fetch was skipped (null = OK to fetch).
	 *
	 * @return string|null
	 */
	public static function get_fetch_block_reason() {
		if ( self::has_linked_lasso_account() ) {
			return 'linked_lasso_account';
		}

		$site_id = self::get_tracking_site_id();
		if ( '' === $site_id ) {
			return 'missing_site_id';
		}
		if ( ! self::is_valid_site_id( $site_id ) ) {
			return 'invalid_site_id';
		}

		return null;
	}

	/**
	 * Click-stream site id (must match analytics lssid).
	 *
	 * @return string
	 */
	public static function get_tracking_site_id() {
		$site_id = License::get_site_id();
		if ( ! is_string( $site_id ) && ! is_numeric( $site_id ) ) {
			$site_id = '';
		}
		$site_id = strtolower( trim( (string) $site_id ) );

		if ( '' === $site_id && defined( 'SITE_ID' ) && is_string( SITE_ID ) && '' !== trim( SITE_ID ) ) {
			$site_id = strtolower( trim( SITE_ID ) );
		}

		return (string) apply_filters( 'lasso_lite_estimate_earning_site_id', $site_id );
	}

	/**
	 * @param string $site_id Site id.
	 * @return bool
	 */
	public static function is_valid_site_id( $site_id ) {
		return is_string( $site_id ) && (bool) preg_match( '/^[a-f0-9]{32}$/i', $site_id );
	}

	/**
	 * API base URL (lasso.link prod; override via LASSO_LITE_API_BASE in wp-config).
	 *
	 * @return string
	 */
	public static function get_api_base() {
		if ( defined( 'LASSO_LITE_API_BASE' ) && is_string( LASSO_LITE_API_BASE ) && LASSO_LITE_API_BASE !== '' ) {
			return rtrim( LASSO_LITE_API_BASE, '/' );
		}

		return rtrim( Constant::LASSO_LINK, '/' );
	}

	/**
	 * Whether cron/API fetch should run for this install.
	 *
	 * @return bool
	 */
	public static function should_fetch() {
		return null === self::get_fetch_block_reason();
	}

	/**
	 * Full POST URL for estimate-earning (for logging / local debugging).
	 *
	 * @return string
	 */
	public static function get_estimate_earning_url() {
		$path = (string) apply_filters( 'lasso_lite_estimate_earning_api_path', '/api/estimate-earning' );
		if ( '/' !== substr( $path, 0, 1 ) ) {
			$path = '/' . $path;
		}

		return self::get_api_base() . $path;
	}

	/**
	 * Cached API payload or null when missing/invalid.
	 *
	 * @return array|null
	 */
	public static function get_cached() {
		$cached = get_transient( self::TRANSIENT_KEY );
		if ( ! is_array( $cached ) || ! isset( $cached['estimated_payout'] ) ) {
			return null;
		}

		return $cached;
	}

	/**
	 * POST /api/estimate-earning and update transient on success.
	 *
	 * On failure, previous cache is preserved.
	 *
	 * @return bool True when a new payload was stored.
	 */
	public static function fetch_and_cache() {
		$block_reason = self::get_fetch_block_reason();
		if ( null !== $block_reason ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log(
					sprintf(
						'estimate-earning skipped: %s site_id=%s account_email=%s account_user_id=%d',
						$block_reason,
						self::get_tracking_site_id(),
						(string) Helper::get_option( Constant::LASSO_ACCOUNT_EMAIL, '' ),
						(int) Helper::get_option( Constant::LASSO_ACCOUNT_USER_ID, 0 )
					)
				);
			}
			return false;
		}

		$site_id = self::get_tracking_site_id();
		$url     = self::get_estimate_earning_url();

		$request_options = array(
			'timeout'   => Constant::TIME_OUT,
			'headers'   => array( 'Content-Type' => 'application/json' ),
			'body'      => wp_json_encode( array( 'site_id' => $site_id ) ),
			'sslverify' => Constant::SSL_VERIFY,
		);
		if ( 0 === strpos( $url, 'http://' ) ) {
			$request_options['sslverify'] = false;
		}

		$res = wp_remote_post( $url, $request_options );

		if ( is_wp_error( $res ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( 'estimate-earning request error: ' . $res->get_error_message() . ' url=' . $url );
			}
			return false;
		}

		$response = array(
			'status_code' => (int) wp_remote_retrieve_response_code( $res ),
			'response'    => json_decode( wp_remote_retrieve_body( $res ) ),
		);

		$status_code = isset( $response['status_code'] ) ? (int) $response['status_code'] : 0;
		if ( 200 !== $status_code ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( 'estimate-earning failed: HTTP ' . $status_code . ' url=' . $url );
			}
			return false;
		}

		$body = $response['response'] ?? null;
		if ( is_object( $body ) ) {
			$body = json_decode( wp_json_encode( $body ), true );
		}
		if ( ! is_array( $body ) || ! isset( $body['estimated_payout'] ) || ! is_numeric( $body['estimated_payout'] ) ) {
			return false;
		}

		$body['estimated_payout'] = (float) $body['estimated_payout'];

		set_transient( self::TRANSIENT_KEY, $body, self::CACHE_TTL );

		return true;
	}

	/**
	 * USD display string for the earnings banner (WP locale when available).
	 *
	 * @param float $amount Payout amount.
	 * @return string
	 */
	public static function format_payout_for_display( $amount ) {
		$amount = (float) $amount;
		if ( function_exists( 'number_format_i18n' ) ) {
			return '$' . number_format_i18n( $amount, 2 );
		}

		return '$' . number_format( $amount, 2, '.', ',' );
	}

	/**
	 * Shape cached API data for the admin earnings notification AJAX/JS.
	 *
	 * @param array $cached Cached API body.
	 * @return array
	 */
	public static function format_for_ui( array $cached ) {
		$payout = (float) ( $cached['estimated_payout'] ?? 0 );

		return array(
			'estimated_payout'   => $payout,
			'payout_30d'         => self::format_payout_for_display( $payout ),
			'earnings_display'   => self::format_payout_for_display( $payout ),
			'total_plus_clicks'  => isset( $cached['total_plus_clicks'] ) ? (int) $cached['total_plus_clicks'] : 0,
			'asin_count'         => isset( $cached['asin_count'] ) ? (int) $cached['asin_count'] : 0,
			'data_as_of'         => $cached['data_as_of'] ?? '',
			'date_from'          => $cached['date_from'] ?? '',
			'date_to'            => $cached['date_to'] ?? '',
			'window_days'        => isset( $cached['window_days'] ) ? (int) $cached['window_days'] : 30,
		);
	}
}
