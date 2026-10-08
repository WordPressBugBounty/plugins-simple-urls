/**
 * Lite wp-admin PostHog capture helper (admin only; loaded with PostHog init in footer).
 */
(function (window) {
	'use strict';

	window.lasso_lite_product_analytics_track = function (eventName, properties) {
		try {
			if (!eventName || !window.posthog || typeof window.posthog.capture !== 'function') {
				return;
			}
			window.posthog.capture(eventName, properties || {});
		} catch (e) {}
	};
})(window);
