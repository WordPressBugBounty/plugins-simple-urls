<?php
/**
 * Lite wp-admin PostHog product analytics (pairs with Pro affiliate-plugin#5910).
 *
 * @package Classes
 */

namespace LassoLite\Classes;

use LassoLite\Admin\Constant;
use LassoLite\Classes\Enum;

/**
 * PostHog loader + markup helpers for Lasso Lite admin only.
 */
class Product_Analytics {

	/**
	 * Whether product analytics is enabled in plugin settings.
	 *
	 * @return bool
	 */
	public static function is_enabled() {
		$settings = Setting::get_settings();
		if ( array_key_exists( 'product_analytics', $settings ) ) {
			return (bool) $settings['product_analytics'];
		}
		return true;
	}

	/**
	 * Whether PostHog should load on the current admin request.
	 *
	 * @param Setting|null $setting Optional setting instance (for tests).
	 * @return bool
	 */
	public static function should_load( $setting = null ) {
		if ( null === $setting && ! is_admin() ) {
			return false;
		}

		if ( ! self::is_enabled() ) {
			return false;
		}

		$project_key = Constant::get_posthog_project_key();
		if ( '' === $project_key ) {
			return false;
		}

		if ( null === $setting ) {
			$setting = new Setting();
		}

		return $setting->is_surls_page();
	}

	/**
	 * Footer script markup for PostHog init + identify, or empty when not loading.
	 *
	 * @param Setting|null $setting Optional setting instance (for tests).
	 * @return string
	 */
	public static function get_footer_script_markup( $setting = null ) {
		if ( ! self::should_load( $setting ) ) {
			return '';
		}

		$settings    = Setting::get_settings();
		$user_email  = get_option( 'admin_email' ); // phpcs:ignore
		$user_email  = strtolower( trim( (string) get_option( 'lasso_license_email', $user_email ) ) );
		$user_hash   = $settings[ Enum::USER_HASH ] ?? '';
		$distinct_id = is_string( $user_hash ) && '' !== $user_hash ? $user_hash : md5( $user_email );
		$site_id     = Helper::get_option( Constant::SITE_ID_KEY );
		$api_host    = Constant::get_posthog_api_host();
		$project_key = Constant::get_posthog_project_key();
		$user        = get_user_by( 'email', $user_email );
		$user_name   = isset( $user->display_name ) ? $user->display_name : get_bloginfo( 'name' );

		$traits = array(
			'name'           => $user_name,
			'email_hash'     => $distinct_id,
			'lasso_version'  => (int) LASSO_LITE_VERSION,
			'classic_editor' => Helper::is_classic_editor(),
			'site_id'        => $site_id,
			'plugin_edition' => 'lite',
			'wp_admin_url'   => admin_url(),
		);

		$dashboard_view = false;
		if ( null === $setting ) {
			$setting = new Setting();
		}
		if ( $setting->is_dashboard_page() ) {
			$dashboard_view = true;
		}

		ob_start();
		?>
<script>
(function () {
	try {
		!function(t,e){var o,n,p,r;e.__SV||(window.posthog=e,e._i=[],e.init=function(i,s,a){function g(t,e){var o=e.split(".");2==o.length&&(t=t[o[0]],e=o[1]),t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}}(p=t.createElement("script")).type="text/javascript",p.async=!0,p.src=s.api_host+"/static/array.js",(r=t.getElementsByTagName("script")[0]).parentNode.insertBefore(p,r);var u=e;for(void 0!==a?u=e[a]=[]:a="posthog",u.people=u.people||[],u.toString=function(t){var e="posthog";return"posthog"!==a&&(e+="."+a),t||(e+=" (stub)"),e},u.people.toString=function(){return u.toString(1)+".people (stub)"},o="capture identify alias people.set people.set_once set_config register register_once unregister opt_out_capturing has_opted_out_capturing opt_in_capturing reset isFeatureEnabled onFeatureFlags getFeatureFlag getFeatureFlagPayload reloadFeatureFlags group updateEarlyAccessFeatureEnrollment getEarlyAccessFeatures getActiveMatchingSurveys getSurveys onSessionId".split(" "),n=0;n<o.length;n++)g(u,o[n]);e._i.push([i,s,a])},e.__SV=1)}(document,window.posthog||[]);
		posthog.init(<?php echo wp_json_encode( $project_key ); ?>, {
			api_host: <?php echo wp_json_encode( $api_host ); ?>,
			capture_pageview: false,
			autocapture: false
		});
		posthog.register({ plugin_edition: 'lite' });
		posthog.identify(<?php echo wp_json_encode( $distinct_id ); ?>, <?php echo wp_json_encode( $traits ); ?>);
		<?php if ( $dashboard_view ) : ?>
		if (typeof window.lasso_lite_product_analytics_track === 'function') {
			window.lasso_lite_product_analytics_track('lite_dashboard_view', { surface: 'dashboard' });
		}
		<?php endif; ?>
	} catch (e) {}
})();
</script>
		<?php
		return (string) ob_get_clean();
	}
}
