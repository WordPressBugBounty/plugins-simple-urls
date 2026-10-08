<?php
/**
 * Declare constants
 *
 * @package lasso constants
 */

namespace LassoLite\Admin;

use LassoLite\Classes\Enum;
use LassoLite\Classes\Helper;

/**
 * Browser project key for PostHog.
 *
 * An explicit LASSO_POSTHOG_API_KEY wins. Otherwise a wp-config
 * LASSO_POSTHOG_PROJECT_KEY (the pre-rename override) wins over the bundled default.
 * Passing the bundled default as $api_key still yields the legacy key, so a later
 * read cannot ignore a site that only set the old constant.
 *
 * @param mixed $legacy_project_key LASSO_POSTHOG_PROJECT_KEY when defined, otherwise null.
 * @param mixed $api_key            LASSO_POSTHOG_API_KEY when defined, otherwise null.
 * @return string
 */
function lasso_lite_resolve_posthog_api_key( $legacy_project_key, $api_key = null ) {
	$bundled = 'phc_QZaXdXM6OcqzRiVcTp3dAFJDEwVsqZgNJgar8NzKSot';

	if ( is_string( $api_key ) && '' === $api_key ) {
		return '';
	}
	if ( null === $api_key && is_string( $legacy_project_key ) && '' === $legacy_project_key ) {
		return '';
	}

	$legacy  = is_string( $legacy_project_key ) ? $legacy_project_key : '';
	$api     = is_string( $api_key ) ? $api_key : '';

	if ( '' !== $api && $api !== $bundled ) {
		return $api;
	}
	if ( '' !== $legacy && $legacy !== $api ) {
		return $legacy;
	}
	if ( '' !== $api ) {
		return $api;
	}
	if ( '' !== $legacy ) {
		return $legacy;
	}
	return $bundled;
}

// ? wp-includes/default-constants.php
if ( ! defined( 'SECURE_AUTH_COOKIE' ) ) {
	if ( ! function_exists( 'wp_cookie_constants' ) ) {
		require_once ABSPATH . 'wp-includes/default-constants.php';
	}

	wp_cookie_constants();
}

if ( ! defined( 'SENTRY_LITE_DSN' ) ) {
	define( 'SENTRY_LITE_DSN', 'https://6416a56005214ca4966296529ef86479@o51581.ingest.sentry.io/6568051' );
}

/**
 * PostHog browser ingest (public project key). Override in wp-config.php; align with Pro affiliate-plugin#5910.
 */
if ( ! defined( 'LASSO_POSTHOG_API_HOST' ) ) {
	define( 'LASSO_POSTHOG_API_HOST', 'https://ph.getlasso.co' );
}

if ( ! defined( 'LASSO_POSTHOG_API_KEY' ) ) {
	define(
		'LASSO_POSTHOG_API_KEY',
		lasso_lite_resolve_posthog_api_key(
			defined( 'LASSO_POSTHOG_PROJECT_KEY' ) ? LASSO_POSTHOG_PROJECT_KEY : null
		)
	);
}

/**
 * Deprecated alias of LASSO_POSTHOG_API_KEY for older wp-config overrides.
 *
 * @deprecated  Use LASSO_POSTHOG_API_KEY (Pro parity).
 */
if ( ! defined( 'LASSO_POSTHOG_PROJECT_KEY' ) ) {
	define( 'LASSO_POSTHOG_PROJECT_KEY', LASSO_POSTHOG_API_KEY );
}

class Constant {
	const SENTRY_DSN        = SENTRY_LITE_DSN;
	const DEFAULT_THUMBNAIL = SIMPLE_URLS_URL . '/admin/assets/images/lasso-no-thumbnail.jpg';
	const LASSO_URL         = 'https://getlasso.co';
	const LASSO_SUPPORT_URL = 'https://support.getlasso.co/';
	const LASSO_UPGRADE_URL = 'https://getlasso.co/upgrade/?utm_campaign=lite-upgrade&utm_source=lasso-lite&utm_medium=wordpress';
	const SITE_ID_KEY       = 'site_id';

	const LASSO_PLUS_SIGNUP_URL      = 'https://app.getlasso.co/signup/plus?utm_campaign=lite-upgrade&utm_source=lasso-lite&utm_medium=wordpress';
	const LASSO_AFFILIATE_PLUS_URL   = 'https://app.getlasso.co/plus/';
	const LASSO_ANALYTICS_URL        = 'https://app.getlasso.co/analytics/';

	const DEFAULT_SETTINGS = array(
		// ? display
		'theme_name'                           => Enum::THEME_CACTUS,
		'display_color_main'                   => 'black',
		'display_color_title'                  => 'black',
		'display_color_background'             => 'white',
		'display_color_button'                 => '#22BAA0',
		'display_color_secondary_button'       => '#22BAA0',
		'display_color_button_text'            => 'white',
		'display_color_pros'                   => '#22BAA0',
		'display_color_cons'                   => '#E06470',
		'primary_button_text'                  => 'Buy Now',
		'secondary_button_text'                => 'Our Review',
		'lasso_affiliate_URL'                  => 'https://getlasso.co/',
		'disclosure_text'                      => 'We earn a commission if you make a purchase, at no additional cost to you.',
		'show_price'                           => true,
		'show_disclosure'                      => true,
		'enable_brag_mode'                     => true,
		'badge_text'                           => '',

		// ? amazon
		'auto_monetize_amazon'                 => true,
		'amazon_access_key_id'                 => '',
		'amazon_secret_key'                    => '',
		'amazon_creators_credential_id'        => '',
		'amazon_creators_secret'               => '',
		'amazon_creators_version'              => '',
		'amazon_creators_partner_tag'          => '',
		'amazon_tracking_id'                   => '',
		'amazon_default_tracking_country'      => 'us',
		'amazon_pricing_daily'                 => true,
		'auto_upgrade_eligible_links'          => true,

		// ? general
		'license_serial'                       => '',
		'general_disable_amazon_notifications' => false,
		'general_disable_tooltip'              => false,
		'general_disable_notification'         => false,
		'general_enable_new_ui'                => true,
		'check_duplicate_link'                 => false,
		'performance_event_tracking'           => true,
		'product_analytics'                    => true,

		// ? url detail
		'enable_nofollow'                      => true,
		'open_new_tab'                         => true,
		'enable_sponsored'                     => true,

		Enum::SUPPORT_ENABLED                  => false,
		Enum::IS_SUBSCRIBE                     => false,
		Enum::EMAIL_SUPPORT                    => '',
		Enum::CUSTOMER_FLOW_ENABLED            => false,
	);

	const LASSO_INTERCOM_APP_ID = 'az01idfr';
	const JWT_SECRET_KEY        = '6KpRcC60EgicHWhyEIqj';
	/** Default FastAPI base when wp-config does not define LASSO_LINK. Use {@see self::get_lasso_link()}. */
	const LASSO_LINK            = 'https://lasso.link';
	const SSL_VERIFY            = true;
	const TIME_OUT              = 30;

	const LASSO_AMAZON_PRODUCTS_DB  = 'lasso_lite_amazon_products';
	const LASSO_POST_TYPE           = 'surl';
	const LASSO_BRAND               = 'Lasso';
	const LASSO_AMAZON_PRODUCT_TYPE = 'Amazon Product';
	const LASSO_CATEGORY            = 'lasso-lite-cat';
	const LASSO_PRO_POST_TYPE       = 'lasso-urls';

	// #1077 review card. New keys on purpose: dismiss state from the old inline notice does not carry over.
	const LASSO_OPTION_REVIEW_DISABLED                     = 'review_card_disabled';
	const LASSO_OPTION_REVIEW_MILESTONE                    = 'review_card_milestone';
	const LASSO_OPTION_REVIEW_SNOOZED_AT                   = 'review_card_snoozed_at';
	const REVIEW_MILESTONES                                = array( 5, 50, 100, 200, 300, 500 );
	const REVIEW_SNOOZE_COOLDOWN                           = 604800; // 7 days: never re-ask sooner, however fast links are added.
	const LASSO_OPTION_AMAZON_CREDENTIALS_NOTICE_DISMISSED = 'dismiss_amazon_credentials_update_notice';
	/** Second dismiss; banner hides only when both dismiss flags are set. */
	const LASSO_OPTION_AMAZON_CREDENTIALS_NOTICE_DISMISSED_FINAL = 'dismiss_amazon_credentials_update_notice_final';
	const LASSO_OPTION_AMAZON_CREDENTIALS_UPDATED          = 'updated_amazon_credentials_notice';
	const LASSO_OPTION_AMAZON_CREATORS_VERIFIED_SIGNATURE  = 'amazon_creators_verified_signature';
	const LASSO_OPTION_DISMISS_PERFORMANCE_NOTICE = 'dismiss_performance_notice';
	const LASSO_LITE_NONCE                        = 'simple-urls-nonce';
	const LASSO_OPTION_AFFILIATE_PROMOTIONS       = 'affiliate_promotions';
	const LASSO_OPTION_DISMISS_PROMOTIONS         = 'dismiss_aff_promotions_notice';
	/** Daily cron: site has zero published links and a valid admin email. */
	const LASSO_OPTION_DORMANT_REENGAGE_ACTIVE    = 'dormant_reengage_active';
	const LASSO_OPTION_DISMISS_DORMANT_REENGAGE   = 'dismiss_dormant_reengage_notice';
	const LASSO_OPTION_IS_CONNECTED_AFFILIATE     = 'is_connected_aff';
	const LASSO_CHECKOUT_URL_DEFAULT              = 'https://getlasso.co/upgrade/?utm_campaign=lite-upgrade&utm_source=lasso-lite&utm_medium=wordpress';

	/** Option keys (not merged into lassolite_settings) for realtime click queue + secrets. */
	const OPTION_REALTIME_CHANNEL_ID    = 'lasso_lite_realtime_channel_id';
	const OPTION_REALTIME_INGEST_SECRET = 'lasso_lite_realtime_ingest_secret';
	const OPTION_REALTIME_CLICK_QUEUE   = 'lasso_lite_realtime_click_queue';

	/** Activation funnel: fire install on next admin load after activation. */
	const OPTION_FUNNEL_PENDING_INSTALL = 'lasso_lite_funnel_pending_install';
	/** Activation funnel: one-time steps already recorded (install, welcome_view, …). */
	const OPTION_FUNNEL_EVENTS_RECORDED = 'lasso_lite_funnel_events_recorded';
	/** Activation funnel: Hub forwards awaiting successful POST (retry on failure). */
	const OPTION_FUNNEL_FORWARD_QUEUE   = 'lasso_lite_funnel_forward_queue';

	/** Default Hub app URL; use {@see self::get_lasso_hub_url()} for the effective base URL. */
	const LASSO_HUB_URL         = 'https://app.getlasso.co';
	const LASSO_ACCOUNT_EMAIL   = 'lasso_account_email';
	const LASSO_ACCOUNT_API_KEY = 'lasso_account_api_key';
	const LASSO_ACCOUNT_USER_ID = 'lasso_account_user_id';

	const BLOCK_CUSTOMIZE = array(
		'single'            => array(
			'type'       => 'single',
			'name'       => 'Single Product Displays',
			'attributes' => array(
				array(
					'name' => 'Product title',
					'attr' => 'title',
					'desc' => 'Override the product title.<br/>Hide the product title by <code>hide</code> value.',
				),
				array(
					'name' => 'Title url',
					'attr' => 'title_url',
					'desc' => 'Customize the title link destination. Leave blank for the default.<br/>Example: https://getlasso.co',
				),
				array(
					'name' => 'Title tag',
					'attr' => 'title_type',
					'desc' => 'Set the title tag. Common values are: <i>H1, H2, H3, H4</i>',
				),
				array(
					'name' => 'Product description',
					'attr' => 'description',
					'desc' => 'Override the product description<br/>Hide the product description by <code>hide</code> value.',
				),
				array(
					'name' => 'Badge',
					'attr' => 'badge',
					'desc' => 'Override the display badge.<br/>Hide the badge by <code>hide</code> value.',
				),
				array(
					'name' => 'Brag',
					'attr' => 'brag',
					'desc' => 'Promote Lasso on your display and <a href="https://getlasso.co/affiliate-program/" rel="nofollow noopener noreferrer" target="_blank">earn money</a> by <code>true</code> value.',
				),
				array(
					'name' => 'Show/Hide the price',
					'attr' => 'price',
				),
				array(
					'name' => 'First button url',
					'attr' => 'primary_url',
					'desc' => 'Override the first button url.<br/>Example: https://getlasso.co',
				),
				array(
					'name' => 'First button text',
					'attr' => 'primary_text',
					'desc' => 'Override the first button text.',
				),
				array(
					'name' => 'Image url',
					'attr' => 'image_url',
					'desc' => 'Override the image url.<br/>Example: https://getlasso.co/lasso.png',
				),
				array(
					'name' => 'Anchor id',
					'attr' => 'anchor_id',
					'desc' => 'Override the anchor id.<br/>Example: the-anchor-link-id',
				),
			),
		),
		'all_attributes'    => array(
			'title',
			'title_url',
			'title_type',
			'description',
			'badge',
			'price',
			'field',
			'rating',
			'theme',
			'primary_url',
			'primary_text',
			'secondary_url',
			'secondary_text',
			'image_url',
			'disclosure_text',
			'button_type',
			'columns',
			'limit',
			'bullets',
			'compact',
			'brag',
			'anchor_id',
		),
		'toogle_attributes' => array(
			'price',
			'field',
			'rating',
		),
		'notice'            => 'For a detailed list of all customization options, visit our <a href="https://support.getlasso.co/en/articles/4575092-shortcode-reference-guide" target="_blank">Shortcode Reference Guide</a>.',
	);

	/**
	 * Effective FastAPI / lasso.link base (no trailing slash).
	 * If wp-config defines LASSO_LINK, use that; otherwise {@see self::LASSO_LINK}.
	 *
	 * @return string
	 */
	public static function get_lasso_link() {
		// constant() avoids resolving bare LASSO_LINK to this class const.
		if ( defined( 'LASSO_LINK' ) ) {
			$override = constant( 'LASSO_LINK' );
			if ( is_string( $override ) && '' !== trim( $override ) ) {
				return rtrim( $override, '/' );
			}
		}

		return rtrim( self::LASSO_LINK, '/' );
	}

	/**
	 * Hub base URL without trailing slash. Override via `LASSO_LITE_HUB_URL` (wp-config.php) or `lasso_lite_hub_url`.
	 *
	 * @return string
	 */
	public static function get_lasso_hub_url() {
		$url = self::LASSO_HUB_URL;
		if ( defined( 'LASSO_LITE_HUB_URL' ) && is_string( LASSO_LITE_HUB_URL ) && LASSO_LITE_HUB_URL !== '' ) {
			$url = LASSO_LITE_HUB_URL;
		}
		if ( function_exists( 'apply_filters' ) ) {
			$url = (string) apply_filters( 'lasso_lite_hub_url', $url );
		}
		return rtrim( $url, '/' );
	}

	/**
	 * PostHog API host (defaults to ph.* mirror of Hub URL when unset).
	 *
	 * @return string
	 */
	public static function get_posthog_api_host() {
		$host = LASSO_POSTHOG_API_HOST;
		if ( is_string( $host ) && '' !== $host ) {
			return rtrim( $host, '/' );
		}

		$hub = self::get_lasso_hub_url();
		if ( false !== strpos( $hub, '://app.' ) ) {
			return str_replace( '://app.', '://ph.', $hub );
		}

		return 'https://ph.getlasso.co';
	}

	/**
	 * PostHog project API key (browser-safe). Filterable for tests.
	 *
	 * @return string
	 */
	public static function get_posthog_project_key() {
		$legacy = defined( 'LASSO_POSTHOG_PROJECT_KEY' ) ? LASSO_POSTHOG_PROJECT_KEY : null;
		$api    = defined( 'LASSO_POSTHOG_API_KEY' ) ? LASSO_POSTHOG_API_KEY : null;
		$key    = lasso_lite_resolve_posthog_api_key( $legacy, $api );
		if ( function_exists( 'apply_filters' ) ) {
			$key = (string) apply_filters( 'lasso_lite_posthog_project_key', $key );
		}
		return is_string( $key ) ? $key : '';
	}

	/**
	 * Lite upgrade URL with a stable cta_id for attribution (#689).
	 *
	 * @param string $cta_id Stable button identifier.
	 * @return string
	 */
	public static function get_lasso_upgrade_url( $cta_id ) {
		if ( ! is_string( $cta_id ) || '' === $cta_id ) {
			return self::LASSO_UPGRADE_URL;
		}

		return Helper::add_params_to_url(
			self::LASSO_UPGRADE_URL,
			array(
				'cta_id' => $cta_id,
			)
		);
	}
}

// Path for vanity JS served by the plugin and its source file location
if ( ! defined( 'LASSO_SNIPPET_VANITY_PATH_LITE' ) ) {
	define( 'LASSO_SNIPPET_VANITY_PATH_LITE', '/js/snippet.min.js' );
}
if ( ! defined( 'LASSO_CONNECT_SNIPPET_FILE_LITE' ) ) {
	define( 'LASSO_CONNECT_SNIPPET_FILE_LITE', SIMPLE_URLS_PLUGIN_PATH . '/admin/assets/js/connect-snippet.min.js' );
}

if ( ! defined( 'LASSO_BEACON_VANITY_PATH' ) ) {
	define( 'LASSO_BEACON_VANITY_PATH', '/js/e' );
}

if ( ! defined( 'LASSO_BEACON_QUERY' ) ) {
	define( 'LASSO_BEACON_QUERY', 'lasso_js_e' );
}
