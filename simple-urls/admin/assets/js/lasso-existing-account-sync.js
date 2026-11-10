/**
 * Silent existing-account sync success handler (#1178).
 *
 * @param {Object} resp AJAX JSON response.
 * @param {Object} deps Injectable deps for unit tests.
 * @returns {boolean} True when a reload was triggered.
 */
(function (root, factory) {
	var fn = factory();
	if (typeof module === 'object' && module.exports) {
		module.exports = fn;
	}
	if (root) {
		root.lassoLiteApplyExistingAccountSyncResponse = fn;
	}
})(
	typeof window !== 'undefined' ? window : this,
	function () {
		return function lassoLiteApplyExistingAccountSyncResponse(resp, deps) {
			deps = deps || {};
			var optionsData =
				typeof deps.optionsData !== 'undefined'
					? deps.optionsData
					: typeof window !== 'undefined'
					? window.lassoLiteOptionsData
					: null;
			var getReloadFlag =
				deps.getReloadFlag ||
				function () {
					return !!(
						typeof window !== 'undefined' &&
						window.__lassoLiteExistingAccountSyncReloaded
					);
				};
			var setReloadFlag =
				deps.setReloadFlag ||
				function (value) {
					if (typeof window !== 'undefined') {
						window.__lassoLiteExistingAccountSyncReloaded = value;
					}
				};
			var reload =
				deps.reload ||
				function () {
					if (typeof window !== 'undefined' && window.location) {
						window.location.reload();
					}
				};

			if (!resp || !resp.success || !resp.data) {
				return false;
			}
			if (!resp.data.synced) {
				return false;
			}
			var syncedUserId = parseInt(resp.data.user_id || 0, 10) || 0;
			if (syncedUserId <= 0) {
				return false;
			}
			if (optionsData) {
				optionsData.userId = syncedUserId;
			}
			if (getReloadFlag()) {
				return false;
			}
			setReloadFlag(true);
			reload();
			return true;
		};
	}
);
