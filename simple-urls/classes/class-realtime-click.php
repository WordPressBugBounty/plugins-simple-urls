<?php
/**
 * Realtime click ingest + queue for dashboard updates.
 *
 * @package LassoLite\Classes
 */

namespace LassoLite\Classes;

use LassoLite\Admin\Constant;

/**
 * Realtime_Click
 */
class Realtime_Click {

	const QUEUE_MAX        = 100;
	const RATE_MAX         = 120;
	const RATE_WINDOW      = 60;
	const TS_SKEW          = 300;
	const POLL_INTERVAL_MS = 12000;

	/** Beacon token bucket length (seconds); token embedded in page remains valid a few buckets. */
	const BEACON_BUCKET_SECONDS = 600;

	/**
	 * Build public beacon token for inline script (no Web Crypto on the client).
	 *
	 * @param string   $channel_id Site channel id.
	 * @param string   $secret     Ingest secret.
	 * @param int|null $bucket     Time bucket or null for current.
	 * @return array{ token: string, bucket: int }
	 */
	public static function get_beacon_credentials( $channel_id, $secret, $bucket = null ) {
		if ( null === $bucket ) {
			$bucket = (int) floor( time() / self::BEACON_BUCKET_SECONDS );
		} else {
			$bucket = (int) $bucket;
		}
		$token = hash_hmac( 'sha256', $channel_id . '|' . $bucket . '|lasso-rt-v1', $secret );

		return array(
			'token'  => $token,
			'bucket' => $bucket,
		);
	}

	/**
	 * Verify time-bucket beacon from front end (HTTP-safe; no SubtleCrypto).
	 *
	 * @param string $channel_id   Channel id from request.
	 * @param int    $beacon_bucket Bucket the client was issued.
	 * @param string $beacon_token  Token from client.
	 * @return bool
	 */
	public static function verify_beacon_token( $channel_id, $beacon_bucket, $beacon_token ) {
		$secret = self::get_ingest_secret();
		if ( '' === $secret || ! is_string( $beacon_token ) || '' === $beacon_token ) {
			return false;
		}
		if ( ! is_string( $channel_id ) || self::get_channel_id() !== $channel_id ) {
			return false;
		}
		$bucket = (int) $beacon_bucket;
		$now_b  = (int) floor( time() / self::BEACON_BUCKET_SECONDS );
		if ( abs( $now_b - $bucket ) > 2 ) {
			return false;
		}
		$expected = hash_hmac( 'sha256', $channel_id . '|' . $bucket . '|lasso-rt-v1', $secret );

		return hash_equals( $expected, $beacon_token );
	}

	/**
	 * Ingest + dashboard poll run when Advanced Click Tracking is enabled (no separate user setting).
	 *
	 * @return bool
	 */
	public static function is_ingest_enabled() {
		$settings = Setting::get_settings();

		return ! empty( $settings['performance_event_tracking'] );
	}

	/**
	 * Stable per-site channel id for signing and dashboard subscription.
	 *
	 * @return string
	 */
	public static function get_channel_id() {
		$id = get_option( Constant::OPTION_REALTIME_CHANNEL_ID, '' );
		if ( ! is_string( $id ) || '' === $id ) {
			$id = function_exists( 'wp_generate_uuid4' ) ? wp_generate_uuid4() : wp_hash( uniqid( (string) wp_rand(), true ) );
			update_option( Constant::OPTION_REALTIME_CHANNEL_ID, $id, false );
		}

		return $id;
	}

	/**
	 * Shared secret for HMAC ingest (lazy-created when realtime is on).
	 *
	 * @return string
	 */
	public static function get_ingest_secret() {
		if ( ! self::is_ingest_enabled() ) {
			return '';
		}

		$secret = get_option( Constant::OPTION_REALTIME_INGEST_SECRET, '' );
		if ( ! is_string( $secret ) || '' === $secret ) {
			$secret = wp_generate_password( 64, true, true );
			update_option( Constant::OPTION_REALTIME_INGEST_SECRET, $secret, false );
		}

		return $secret;
	}

	/**
	 * Build config for wp_localize_script (admin).
	 *
	 * @return array
	 */
	public static function get_js_config() {
		$settings = Setting::get_settings();
		if ( empty( $settings['performance_event_tracking'] ) ) {
			return array(
				'mode' => 'off',
			);
		}

		$queue     = get_option( Constant::OPTION_REALTIME_CLICK_QUEUE, array() );
		$start_seq = ( is_array( $queue ) && isset( $queue['seq'] ) ) ? (int) $queue['seq'] : 0;

		$config = array(
			'mode'         => 'poll',
			'pollInterval' => self::POLL_INTERVAL_MS,
			'pullAction'   => 'lasso_lite_realtime_pull',
			'channelId'    => self::get_channel_id(),
			'startSeq'     => $start_seq,
		);

		/**
		 * Filter dashboard live-click poll config (admin scripts).
		 *
		 * @param array $config Keys: mode, pollInterval, pullAction, channelId, startSeq.
		 */
		return apply_filters( 'lasso_lite_realtime_js_config', $config );
	}

	/**
	 * Verify HMAC for ingest body.
	 *
	 * @param string $channel_id Channel id.
	 * @param int    $ts         Unix timestamp from client.
	 * @param int    $lid        Link id.
	 * @param string $type       Event type string.
	 * @param string $sig        Hex signature from client.
	 * @return bool
	 */
	public static function verify_signature( $channel_id, $ts, $lid, $type, $sig ) {
		$secret = self::get_ingest_secret();
		if ( '' === $secret || ! is_string( $sig ) || '' === $sig ) {
			return false;
		}
		$now = time();
		if ( abs( $now - (int) $ts ) > self::TS_SKEW ) {
			return false;
		}
		if ( ! is_string( $channel_id ) || self::get_channel_id() !== $channel_id ) {
			return false;
		}
		$message  = $channel_id . '|' . (string) (int) $ts . '|' . (string) (int) $lid . '|' . (string) $type;
		$expected = hash_hmac( 'sha256', $message, $secret );

		return hash_equals( strtolower( $expected ), strtolower( $sig ) );
	}

	/**
	 * Rate limit ingest by IP.
	 *
	 * @return bool True if allowed.
	 */
	public static function rate_limit_allow() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		if ( '' === $ip ) {
			return false;
		}
		$key   = 'lasso_rt_ing_' . md5( $ip );
		$count = (int) get_transient( $key );
		if ( $count >= self::RATE_MAX ) {
			return false;
		}
		set_transient( $key, $count + 1, self::RATE_WINDOW );

		return true;
	}

	/**
	 * Append event to bounded queue.
	 *
	 * @param int    $lid  Link id.
	 * @param string $type Event type.
	 * @return int New sequence number.
	 */
	public static function enqueue_event( $lid, $type ) {
		$queue = get_option( Constant::OPTION_REALTIME_CLICK_QUEUE, null );
		if ( ! is_array( $queue ) ) {
			$queue = array(
				'seq'    => 0,
				'events' => array(),
			);
		}
		$seq = isset( $queue['seq'] ) ? (int) $queue['seq'] : 0;
		++$seq;
		$event    = array(
			'seq' => $seq,
			'ts'  => time(),
			'lid' => (int) $lid,
			'evt' => is_string( $type ) ? substr( $type, 0, 120 ) : '',
		);
		$events   = isset( $queue['events'] ) && is_array( $queue['events'] ) ? $queue['events'] : array();
		$events[] = $event;
		if ( count( $events ) > self::QUEUE_MAX ) {
			$events = array_slice( $events, - self::QUEUE_MAX );
		}
		$queue['seq']    = $seq;
		$queue['events'] = $events;
		update_option( Constant::OPTION_REALTIME_CLICK_QUEUE, $queue, false );

		return $seq;
	}

	/**
	 * Return events with seq > since.
	 *
	 * @param int $since Last seen sequence.
	 * @return array{ events: array, max_seq: int }
	 */
	public static function pull_events_since( $since ) {
		$queue = get_option( Constant::OPTION_REALTIME_CLICK_QUEUE, null );
		if ( ! is_array( $queue ) || empty( $queue['events'] ) || ! is_array( $queue['events'] ) ) {
			return array(
				'events'  => array(),
				'max_seq' => (int) ( $queue['seq'] ?? 0 ),
			);
		}
		$since = (int) $since;
		$out   = array();
		foreach ( $queue['events'] as $ev ) {
			if ( is_array( $ev ) && isset( $ev['seq'] ) && (int) $ev['seq'] > $since ) {
				$out[] = $ev;
			}
		}
		$max_seq = isset( $queue['seq'] ) ? (int) $queue['seq'] : 0;

		return array(
			'events'  => $out,
			'max_seq' => $max_seq,
		);
	}
}
