<?php
/**
 * Lite activation funnel admin events (taxonomy + Hub forward).
 *
 * @package LassoLite\Classes
 */

namespace LassoLite\Classes;

use LassoLite\Admin\Constant;

/**
 * Activation_Funnel
 */
class Activation_Funnel {

	const EVENT_INSTALL         = 'install';
	const EVENT_WELCOME_VIEW    = 'welcome_view';
	const EVENT_CONNECT_SUCCESS = 'connect_success';
	const EVENT_FIRST_LINK      = 'first_link';
	const EVENT_DISPLAY_RENDER  = 'display_render';
	const EVENT_UPGRADE_CLICK   = 'upgrade_click';

	/**
	 * Whether a shutdown flush is already scheduled for this request.
	 *
	 * @var bool
	 */
	private static $forward_flush_scheduled = false;

	/**
	 * Canonical funnel event names (locked taxonomy).
	 *
	 * @return string[]
	 */
	public static function get_event_names() {
		return array(
			self::EVENT_INSTALL,
			self::EVENT_WELCOME_VIEW,
			self::EVENT_CONNECT_SUCCESS,
			self::EVENT_FIRST_LINK,
			self::EVENT_DISPLAY_RENDER,
			self::EVENT_UPGRADE_CLICK,
		);
	}

	/**
	 * Whether the event name is part of the locked taxonomy.
	 *
	 * @param string $event_name Event name.
	 * @return bool
	 */
	public static function is_valid_event_name( $event_name ) {
		return in_array( $event_name, self::get_event_names(), true );
	}

	/**
	 * Config passed to admin JS (taxonomy + site id).
	 *
	 * @return array<string, mixed>
	 */
	public static function get_js_config() {
		return array(
			'events'  => self::get_event_names(),
			'site_id' => Estimate_Earning::get_tracking_site_id(),
		);
	}

	/**
	 * Provision tracking site_id when missing (fresh installs).
	 *
	 * @return bool True when a valid 32-char hex site id is available.
	 */
	public static function ensure_tracking_site_id() {
		$site_id = Estimate_Earning::get_tracking_site_id();
		if ( Estimate_Earning::is_valid_site_id( $site_id ) ) {
			return true;
		}

		License::lasso_getinfo( array( 'license_key' ) );
		$site_id = Estimate_Earning::get_tracking_site_id();

		return Estimate_Earning::is_valid_site_id( $site_id );
	}

	/**
	 * Build normalized admin-event payload.
	 *
	 * @param string               $event_name Event name.
	 * @param array<string, mixed> $metadata   Step metadata.
	 * @return array<string, mixed>
	 */
	public static function build_payload( $event_name, $metadata = array() ) {
		$metadata = is_array( $metadata ) ? $metadata : array();

		$payload = array(
			'event'     => $event_name,
			'site_id'   => Estimate_Earning::get_tracking_site_id(),
			'timestamp' => gmdate( 'c' ),
			'metadata'  => $metadata,
			'source'    => 'lasso-lite',
		);

		if ( self::EVENT_UPGRADE_CLICK === $event_name && isset( $metadata['cta_id'] ) ) {
			$payload['cta_id'] = sanitize_text_field( (string) $metadata['cta_id'] );
		}

		/**
		 * Filter activation funnel payload before record/forward.
		 *
		 * @param array  $payload    Normalized payload.
		 * @param string $event_name Event name.
		 */
		return (array) apply_filters( 'lasso_lite_activation_funnel_payload', $payload, $event_name );
	}

	/**
	 * Record and optionally forward an admin funnel event.
	 *
	 * @param string               $event_name Event name.
	 * @param array<string, mixed> $metadata   Step metadata.
	 * @param bool                 $forward    POST to Hub when true.
	 * @return array<string, mixed>|false Payload on success; false when invalid or deduped.
	 */
	public static function track_admin_event( $event_name, $metadata = array(), $forward = true ) {
		if ( ! self::is_valid_event_name( $event_name ) ) {
			return false;
		}

		if ( self::EVENT_UPGRADE_CLICK !== $event_name && self::was_event_recorded( $event_name ) ) {
			return false;
		}

		if ( self::EVENT_UPGRADE_CLICK !== $event_name && self::is_event_in_forward_queue( $event_name ) ) {
			return false;
		}

		if ( $forward ) {
			self::ensure_tracking_site_id();
		}

		$payload = self::build_payload( $event_name, $metadata );

		/**
		 * Fires when a funnel admin event is recorded (tests + extensions).
		 *
		 * @param string               $event_name Event name.
		 * @param array<string, mixed> $payload    Normalized payload.
		 */
		do_action( 'lasso_lite_track_activation_funnel', $event_name, $payload );

		if ( $forward ) {
			if ( ! Estimate_Earning::is_valid_site_id( $payload['site_id'] ?? '' ) ) {
				return false;
			}
			self::enqueue_forward( $event_name, $payload );
		} elseif ( self::EVENT_UPGRADE_CLICK !== $event_name ) {
			self::mark_event_recorded( $event_name );
		}

		return $payload;
	}

	/**
	 * Fire pending install event after plugin activation (first admin request).
	 *
	 * @return void
	 */
	public static function maybe_track_pending_install() {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$pending = get_option( Constant::OPTION_FUNNEL_PENDING_INSTALL, 0 );
		if ( ! $pending ) {
			return;
		}

		if ( ! self::ensure_tracking_site_id() ) {
			return;
		}

		delete_option( Constant::OPTION_FUNNEL_PENDING_INSTALL );
		self::track_admin_event( self::EVENT_INSTALL );
	}

	/**
	 * Hub POST URL for admin funnel events.
	 *
	 * @return string
	 */
	public static function get_track_url() {
		$path = (string) apply_filters( 'lasso_lite_activation_funnel_api_path', '/api/lite/admin-events' );
		if ( '/' !== substr( $path, 0, 1 ) ) {
			$path = '/' . $path;
		}

		return rtrim( Constant::get_lasso_hub_url(), '/' ) . $path;
	}

	/**
	 * Queue payload for deferred Hub POST (shutdown flush).
	 *
	 * @param array<string, mixed> $payload Event payload.
	 * @return void
	 */
	public static function forward_payload( $payload ) {
		if ( ! is_array( $payload ) || empty( $payload['event'] ) ) {
			return;
		}

		$event_name = (string) $payload['event'];
		if ( ! self::is_valid_event_name( $event_name ) ) {
			return;
		}

		self::enqueue_forward( $event_name, $payload );
	}

	/**
	 * Retry any queued forwards from prior requests (failed Hub POST).
	 *
	 * @return void
	 */
	public static function maybe_flush_forward_queue() {
		$queue = get_option( Constant::OPTION_FUNNEL_FORWARD_QUEUE, array() );
		if ( ! is_array( $queue ) || empty( $queue ) ) {
			return;
		}
		self::schedule_forward_flush();
	}

	/**
	 * POST payload to Hub; returns true on 2xx.
	 *
	 * @param array<string, mixed> $payload Event payload.
	 * @return bool
	 */
	private static function forward_payload_sync( $payload ) {
		if ( ! is_array( $payload ) || empty( $payload['event'] ) ) {
			return false;
		}

		$url     = self::get_track_url();
		$headers = array(
			'Content-Type' => 'application/json',
		);

		$license = License::get_license();
		if ( is_string( $license ) && '' !== $license ) {
			$headers = array_merge( Helper::get_headers( $license ), $headers );
		}

		$request_options = array(
			'headers'   => $headers,
			'timeout'   => 5,
			'sslverify' => Constant::SSL_VERIFY,
			'body'      => wp_json_encode( $payload ),
		);

		$res = wp_remote_post( $url, $request_options );
		if ( is_wp_error( $res ) ) {
			return false;
		}

		$status = (int) wp_remote_retrieve_response_code( $res );

		return $status >= 200 && $status < 300;
	}

	/**
	 * Append payload to the forward queue and schedule shutdown flush.
	 *
	 * @param string               $event_name Event name.
	 * @param array<string, mixed> $payload    Normalized payload.
	 * @return void
	 */
	private static function enqueue_forward( $event_name, $payload ) {
		$queue = get_option( Constant::OPTION_FUNNEL_FORWARD_QUEUE, array() );
		if ( ! is_array( $queue ) ) {
			$queue = array();
		}

		if ( self::EVENT_UPGRADE_CLICK !== $event_name ) {
			foreach ( $queue as $item ) {
				if ( is_array( $item ) && ( $item['event_name'] ?? '' ) === $event_name ) {
					self::schedule_forward_flush();
					return;
				}
			}
		}

		$queue[] = array(
			'event_name' => $event_name,
			'payload'    => $payload,
		);
		update_option( Constant::OPTION_FUNNEL_FORWARD_QUEUE, $queue, false );
		self::schedule_forward_flush();
	}

	/**
	 * Schedule a single shutdown handler to flush the forward queue.
	 *
	 * @return void
	 */
	private static function schedule_forward_flush() {
		if ( self::$forward_flush_scheduled ) {
			return;
		}
		self::$forward_flush_scheduled = true;
		add_action( 'shutdown', array( __CLASS__, 'flush_forward_queue' ), 20 );
	}

	/**
	 * Send queued Hub events after the admin response is built (non-blocking for page load).
	 *
	 * @return void
	 */
	public static function flush_forward_queue() {
		$queue = get_option( Constant::OPTION_FUNNEL_FORWARD_QUEUE, array() );
		if ( ! is_array( $queue ) || empty( $queue ) ) {
			return;
		}

		$remaining = array();
		foreach ( $queue as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}
			$event_name = (string) ( $item['event_name'] ?? '' );
			$payload    = $item['payload'] ?? array();
			if ( ! is_array( $payload ) || '' === $event_name ) {
				continue;
			}

			if ( self::forward_payload_sync( $payload ) ) {
				if ( self::EVENT_UPGRADE_CLICK !== $event_name ) {
					self::mark_event_recorded( $event_name );
				}
				continue;
			}

			$remaining[] = $item;
		}

		update_option( Constant::OPTION_FUNNEL_FORWARD_QUEUE, $remaining, false );
	}

	/**
	 * Whether a one-time funnel step was already recorded locally.
	 *
	 * @param string $event_name Event name.
	 * @return bool
	 */
	private static function was_event_recorded( $event_name ) {
		$recorded = get_option( Constant::OPTION_FUNNEL_EVENTS_RECORDED, array() );
		if ( ! is_array( $recorded ) ) {
			$recorded = array();
		}

		return in_array( $event_name, $recorded, true );
	}

	/**
	 * Whether a one-time event is waiting on a Hub forward retry.
	 *
	 * @param string $event_name Event name.
	 * @return bool
	 */
	private static function is_event_in_forward_queue( $event_name ) {
		$queue = get_option( Constant::OPTION_FUNNEL_FORWARD_QUEUE, array() );
		if ( ! is_array( $queue ) ) {
			return false;
		}
		foreach ( $queue as $item ) {
			if ( is_array( $item ) && ( $item['event_name'] ?? '' ) === $event_name ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Persist one-time funnel step in options.
	 *
	 * @param string $event_name Event name.
	 * @return void
	 */
	private static function mark_event_recorded( $event_name ) {
		$recorded = get_option( Constant::OPTION_FUNNEL_EVENTS_RECORDED, array() );
		if ( ! is_array( $recorded ) ) {
			$recorded = array();
		}
		if ( in_array( $event_name, $recorded, true ) ) {
			return;
		}
		$recorded[] = $event_name;
		update_option( Constant::OPTION_FUNNEL_EVENTS_RECORDED, $recorded, false );
	}
}
