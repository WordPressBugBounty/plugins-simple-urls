/**
 * Lite Dashboard Intro.js product tour (#1134, parity with Pro #5894).
 * Auto-starts once; only Skip / Done persist via user meta AJAX.
 * Overlay / Esc exit must not mark the tour done.
 * Chat/support step is included only when a launcher is present
 * (Intercom, legacy fake bubble, or Lite #support-launcher).
 */
(function ($) {
	'use strict';

	var CHAT_ANCHOR_ID = 'lasso-tour-chat-anchor';
	var CHAT_LAUNCHER_SELECTORS = [
		'.intercom-lightweight-app-launcher',
		'iframe.intercom-launcher-frame',
		'.intercom-launcher-frame',
		'.intercom-launcher',
		'.fake-intercom-bubble-chat-app-launcher-icon',
		'#fake-intercom-bubble-chat',
		// Lite Support bubble (footer.php) — shown after support.js fades opacity in.
		'#support-launcher'
	];

	function dismissTour() {
		if (typeof lassoProductTour === 'undefined') {
			return;
		}

		$.post(lassoProductTour.ajaxUrl, {
			action: lassoProductTour.action,
			security: lassoProductTour.nonce
		});
	}

	/**
	 * Pick the visible bottom-right chat control — not Intercom's full-viewport wrapper
	 * (which makes Intro.js park the tooltip at top-left).
	 */
	function findVisibleChatLauncher() {
		var candidates = [];
		var i;
		var j;
		var nodes;
		var el;
		var rect;
		var vw = window.innerWidth || document.documentElement.clientWidth;
		var vh = window.innerHeight || document.documentElement.clientHeight;

		for (i = 0; i < CHAT_LAUNCHER_SELECTORS.length; i++) {
			nodes = document.querySelectorAll(CHAT_LAUNCHER_SELECTORS[i]);
			for (j = 0; j < nodes.length; j++) {
				el = nodes[j];
				rect = el.getBoundingClientRect();
				if (rect.width < 24 || rect.height < 24) {
					continue;
				}
				if (rect.bottom < 0 || rect.right < 0 || rect.top > vh || rect.left > vw) {
					continue;
				}
				// Lite #support-launcher starts at opacity:0 for ~2s (support.js).
				try {
					var style = window.getComputedStyle(el);
					if (
						style.display === 'none' ||
						style.visibility === 'hidden' ||
						parseFloat(style.opacity) === 0
					) {
						continue;
					}
				} catch (e) {
					// Ignore computed-style failures; keep size/viewport checks.
				}
				candidates.push({
					el: el,
					rect: rect,
					// Prefer compact buttons in the bottom-right corner.
					score:
						rect.bottom / vh +
						rect.right / vw -
						Math.min(rect.width, 120) / 1000 -
						Math.min(rect.height, 120) / 1000
				});
			}
		}

		if (!candidates.length) {
			return null;
		}

		candidates.sort(function (a, b) {
			return b.score - a.score;
		});

		return candidates[0];
	}

	function syncChatTourAnchor() {
		var found = findVisibleChatLauncher();
		if (!found) {
			return null;
		}

		var rect = found.rect;
		var anchor = document.getElementById(CHAT_ANCHOR_ID);
		if (!anchor) {
			anchor = document.createElement('div');
			anchor.id = CHAT_ANCHOR_ID;
			anchor.setAttribute('aria-hidden', 'true');
			document.body.appendChild(anchor);
		}

		anchor.style.cssText = [
			'position:fixed',
			'left:' + Math.round(rect.left) + 'px',
			'top:' + Math.round(rect.top) + 'px',
			'width:' + Math.round(rect.width) + 'px',
			'height:' + Math.round(rect.height) + 'px',
			'z-index:2147483002',
			'pointer-events:none',
			'margin:0',
			'padding:0',
			'border:0',
			'background:transparent'
		].join(';');

		return '#' + CHAT_ANCHOR_ID;
	}

	function buildSteps() {
		var chatSelector = syncChatTourAnchor();
		var out = [];

		(lassoProductTour.steps || []).forEach(function (step) {
			if (!step) {
				return;
			}

			if (step.lassoChat) {
				if (!chatSelector) {
					return;
				}
				out.push(
					$.extend({}, step, {
						element: chatSelector,
						// Launcher sits bottom-right — keep tooltip above/left of it.
						position: 'top'
					})
				);
				return;
			}

			if (step.element && document.querySelector(step.element)) {
				out.push(step);
			}
		});

		return out;
	}

	function startTour() {
		if (typeof introJs === 'undefined' || typeof lassoProductTour === 'undefined') {
			return;
		}

		var steps = buildSteps();

		if (!steps.length) {
			return;
		}

		var tour = introJs.tour();
		var dismissed = false;
		var scrollTimers = [];

		function clearScrollTimers() {
			while (scrollTimers.length) {
				window.clearTimeout(scrollTimers.pop());
			}
		}

		function persistOnce() {
			if (dismissed) {
				return;
			}
			dismissed = true;
			clearScrollTimers();
			dismissTour();
			var anchor = document.getElementById(CHAT_ANCHOR_ID);
			if (anchor && anchor.parentNode) {
				anchor.parentNode.removeChild(anchor);
			}
		}

		function paintPurpleChrome() {
			// Intro.js v8 stamps .introjs-light on the tour root (white CSS vars).
			var roots = document.querySelectorAll('.introjs-light, .introjs-tooltipReferenceLayer, .introjs-tooltip');
			var vars = {
				'--introjs-bg': '#5e36ca',
				'--introjs-color': '#ffffff',
				'--introjs-arrow-color': '#5e36ca',
				'--introjs-btn-bg': 'rgba(255,255,255,0.14)',
				'--introjs-btn-color': '#ffffff',
				'--introjs-btn-border': 'rgba(255,255,255,0.45)',
				'--introjs-btn-hover-bg': 'rgba(255,255,255,0.24)',
				'--introjs-btn-hover-color': '#ffffff',
				'--introjs-btn-focus-bg': 'rgba(255,255,255,0.24)',
				'--introjs-btn-focus-color': '#ffffff',
				'--introjs-btn-active-bg': 'rgba(255,255,255,0.3)',
				'--introjs-btn-active-color': '#ffffff',
				'--introjs-btn-active-border': 'rgba(255,255,255,0.55)',
				'--introjs-btn-textShadow': 'transparent'
			};
			Array.prototype.forEach.call(roots, function (el) {
				Object.keys(vars).forEach(function (key) {
					el.style.setProperty(key, vars[key], 'important');
				});
				if (el.classList && el.classList.contains('introjs-tooltip')) {
					el.style.setProperty('background-color', '#5e36ca', 'important');
					el.style.setProperty('color', '#ffffff', 'important');
				}
			});
		}

		function openFiltersPanel() {
			var panel = document.getElementById('lasso-dashboard-filters-panel');
			var toggle = document.getElementById('lasso-dashboard-filters-toggle');
			if (!panel) {
				return;
			}
			panel.classList.add('is-open');
			var layout = panel.closest('.lasso-dashboard-layout');
			if (layout) {
				layout.classList.add('lasso-dashboard-layout-panel-open');
			}
			if (toggle) {
				toggle.setAttribute('aria-expanded', 'true');
				toggle.classList.add('is-open');
			}
		}

		function isFiltersStep(targetElement) {
			return (
				targetElement &&
				(targetElement.id === 'lasso-dashboard-filters-toggle' ||
					targetElement.id === 'lasso-dashboard-filters-panel')
			);
		}

		function isChatAnchorStep(targetElement) {
			return targetElement && targetElement.id === CHAT_ANCHOR_ID;
		}

		function getViewportBand() {
			var pad = 24;
			var adminBar = document.getElementById('wpadminbar');
			return {
				topSafe: pad + (adminBar ? adminBar.offsetHeight : 0),
				bottomSafe: window.innerHeight - pad
			};
		}

		function scrollWindowByDelta(delta, behavior) {
			if (!delta) {
				return;
			}
			window.scrollBy({
				top: delta,
				left: 0,
				behavior: behavior || 'smooth'
			});
		}

		/**
		 * Before the step paints, ease the target into a band that leaves room for
		 * a bottom-positioned tooltip. Skip fixed chat anchors (viewport-fixed).
		 */
		function scrollTargetIntoComfortBand(el) {
			if (!el || isChatAnchorStep(el)) {
				return;
			}

			var rect = el.getBoundingClientRect();
			if (!rect.width && !rect.height) {
				return;
			}

			var band = getViewportBand();
			// Room under the target for the purple tooltip card.
			var tooltipRoom = 240;
			var preferredBottom = Math.max(band.topSafe + 80, band.bottomSafe - tooltipRoom);
			var delta = 0;

			if (rect.top < band.topSafe) {
				delta = rect.top - band.topSafe;
			} else if (rect.bottom > preferredBottom) {
				delta = rect.bottom - preferredBottom;
			}

			scrollWindowByDelta(delta, 'smooth');
		}

		/**
		 * Intro.js tooltips live in an absolute layer — own window scroll so we do
		 * not fight Intro.js scrollToElement (which caused jumpy step changes).
		 */
		function ensureTourModalInView(behavior) {
			var tooltip = document.querySelector('.introjs-tooltip');
			if (!tooltip) {
				return;
			}

			var rect = tooltip.getBoundingClientRect();
			if (!rect.width && !rect.height) {
				return;
			}

			var band = getViewportBand();
			var delta = 0;

			if (rect.top < band.topSafe) {
				delta = rect.top - band.topSafe;
			} else if (rect.bottom > band.bottomSafe) {
				delta = rect.bottom - band.bottomSafe;
			}

			if (!delta) {
				return;
			}

			if (rect.height > band.bottomSafe - band.topSafe) {
				delta = rect.top + rect.height / 2 - (band.topSafe + band.bottomSafe) / 2;
			}

			scrollWindowByDelta(delta, behavior || 'smooth');
		}

		function afterStepPaint() {
			paintPurpleChrome();
			clearScrollTimers();
			// One coalesce: wait for Intro.js layout, then a single smooth correction.
			scrollTimers.push(
				window.setTimeout(function () {
					paintPurpleChrome();
					ensureTourModalInView('smooth');
				}, 120)
			);
		}

		// Note: Intro.js v8 `onafterchange` (lowercase) does not return `this` — use onAfterChange.
		tour
			.setOptions({
				steps: steps,
				tooltipClass: 'lasso-product-tour-tooltip',
				// Outside click / Esc must not close permanently — only Skip / Done.
				exitOnOverlayClick: false,
				exitOnEsc: false,
				showProgress: false,
				showBullets: true,
				overlayOpacity: 0.55,
				disableInteraction: true,
				// Own scrolling in onBeforeChange / afterStepPaint — avoids duel with Intro.js.
				scrollToElement: false,
				scrollTo: 'tooltip',
				scrollPadding: 80,
				nextLabel: 'Next',
				prevLabel: 'Back',
				doneLabel: 'Done',
				skipLabel: 'Skip'
			})
			.oncomplete(persistOnce)
			.onskip(persistOnce)
			.onBeforeChange(function (targetElement) {
				clearScrollTimers();
				// Panel is collapsed by default — open before spotlight measures Filters steps.
				if (isFiltersStep(targetElement)) {
					openFiltersPanel();
				}
				// Re-sync fixed anchor to the live Intercom / Support bubble before spotlight.
				if (isChatAnchorStep(targetElement)) {
					syncChatTourAnchor();
				} else {
					scrollTargetIntoComfortBand(targetElement);
				}
			})
			.onAfterChange(function (targetElement) {
				if (isChatAnchorStep(targetElement)) {
					syncChatTourAnchor();
					if (typeof tour.refresh === 'function') {
						tour.refresh();
					}
				}
				afterStepPaint();
			});

		tour.start();
		afterStepPaint();
	}

	function bootTour() {
		// Intercom / Lite #support-launcher (opacity fade at 2s) — wait so chat step can attach.
		var deadline = Date.now() + 3500;

		(function waitForChatOrTimeout() {
			if (findVisibleChatLauncher() || Date.now() >= deadline) {
				window.setTimeout(startTour, 200);
				return;
			}
			window.setTimeout(waitForChatOrTimeout, 150);
		})();
	}

	$(function () {
		bootTour();
	});
})(jQuery);
