/**
 * Lite activation funnel admin events (taxonomy helpers for JS).
 */
(function ($) {
	'use strict';

	function funnelConfig() {
		var opts = window.lassoLiteOptionsData;
		return opts && opts.activation_funnel;
	}

	function isValidEvent(eventName) {
		var cfg = funnelConfig();
		if (!cfg || !cfg.events || !eventName) {
			return false;
		}
		return cfg.events.indexOf(eventName) !== -1;
	}

	window.lasso_lite_track_activation_funnel = function (eventName, metadata) {
		if (!isValidEvent(eventName)) {
			return;
		}
		var opts = window.lassoLiteOptionsData;
		if (!opts || !opts.ajax_url) {
			return;
		}

		var meta = metadata && typeof metadata === 'object' ? metadata : {};
		var payload = {
			action: 'lasso_lite_track_activation_funnel',
			nonce: opts.optionsNonce,
			event: eventName,
			metadata: meta,
		};
		if (meta.cta_id) {
			payload.cta_id = meta.cta_id;
		}

		$.post(opts.ajax_url, payload);
	};

	function parseCtaIdFromHref(href) {
		if (!href) {
			return '';
		}
		try {
			var url = new URL(href, window.location.origin);
			return url.searchParams.get('cta_id') || '';
		} catch (e) {
			return '';
		}
	}

	$(document).ready(function () {
		var opts = window.lassoLiteOptionsData;
		if (opts && opts.is_onboard_page === '1') {
			lasso_lite_track_activation_funnel('welcome_view', {
				step: opts.onboarding_current_step || 'welcome',
			});
		}
	});

	$(document).on('click', 'a[href*="getlasso.co/upgrade"]', function () {
		var href = $(this).attr('href') || '';
		var ctaId = parseCtaIdFromHref(href);
		var meta = {};
		if (ctaId) {
			meta.cta_id = ctaId;
		}
		lasso_lite_track_activation_funnel('upgrade_click', meta);
	});
})(jQuery);
