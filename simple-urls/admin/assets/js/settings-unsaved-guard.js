(function ($) {
	var guard = {
		ignore_before_unload: false,
		pending_action: null,
		initial: null,
	};

	function getFormValues() {
		var values = {};
		$(
			"form.lasso-admin-settings-form input, form.lasso-admin-settings-form select, form.lasso-admin-settings-form textarea," +
				"form.lasso-lite-admin-settings-form input, form.lasso-lite-admin-settings-form select, form.lasso-lite-admin-settings-form textarea"
		).each(function () {
			var $f = $(this);
			var name = $f.attr("name");
			if (!name || $f.attr("type") === "button" || name === "license_serial") {
				return;
			}
			var v;
			if ($f.attr("type") === "checkbox") {
				v = $f.prop("checked") ? 1 : 0;
			} else {
				v = $f.val();
			}
			values[name] = v;
		});
		return values;
	}

	function hasUnsaved() {
		if (!guard.initial) {
			return false;
		}
		var cur = getFormValues();
		return JSON.stringify(cur) !== JSON.stringify(guard.initial);
	}

	function runPending() {
		if (typeof guard.pending_action !== "function") {
			return;
		}
		var fn = guard.pending_action;
		guard.pending_action = null;
		fn();
	}

	function liteTriggerSave() {
		if ($("#btn-save-settings-general").length) {
			$("#btn-save-settings-general").trigger("click");
		} else if ($("#btn-save-settings-display").length) {
			$("#btn-save-settings-display").trigger("click");
		} else if ($(".btn-save-settings-amazon").length) {
			$(".btn-save-settings-amazon").trigger("click");
		}
	}

	function showModal(action) {
		var modal = $("#unsaved-changes");
		if (!modal.length) {
			action();
			return;
		}
		guard.pending_action = action;

		modal.find(".close")
			.off("click.liteSettingsUnsavedClose")
			.on("click.liteSettingsUnsavedClose", function () {
				guard.pending_action = null;
			});

		modal.find(".btn-outline-secondary")
			.off("click.liteSettingsUnsaved")
			.on("click.liteSettingsUnsaved", function () {
				guard.ignore_before_unload = true;
				modal.modal("hide");
				runPending();
				setTimeout(function () {
					guard.ignore_before_unload = false;
				}, 0);
			});

		modal.find(".green-bg")
			.off("click.liteSettingsUnsaved")
			.on("click.liteSettingsUnsaved", function () {
				modal.modal("hide");
				liteTriggerSave();
			});

		modal.modal("show");
	}

	function bindNavGuard() {
		$(document)
			.off("click.liteSettingsLeave", "a[href]")
			.on("click.liteSettingsLeave", "a[href]", function (e) {
				var link = $(this);
				var href = link.attr("href");
				if (
					!hasUnsaved() ||
					!href ||
					href === "#" ||
					href.indexOf("javascript:") === 0 ||
					link.attr("target") === "_blank" ||
					link.closest("#unsaved-changes").length ||
					link.closest("#url-save").length
				) {
					return;
				}
				e.preventDefault();
				showModal(function () {
					guard.ignore_before_unload = true;
					window.location.href = href;
				});
			});
	}

	$(function () {
		if (!$("#unsaved-changes").length) {
			return;
		}
		if (
			!$("form.lasso-admin-settings-form, form.lasso-lite-admin-settings-form")
				.length
		) {
			return;
		}

		guard.initial = getFormValues();

		window.addEventListener("beforeunload", function (e) {
			if (guard.ignore_before_unload || !hasUnsaved()) {
				return;
			}
			e.preventDefault();
			e.returnValue = "";
		});

		bindNavGuard();

		$(document).ajaxComplete(function (event, xhr, settings) {
			if (!settings.data || typeof settings.data !== "string") {
				return;
			}
			var data = settings.data;
			if (
				data.indexOf("action=lasso_lite_save_settings_general") === -1 &&
				data.indexOf("action=lasso_lite_save_settings_amazon") === -1 &&
				data.indexOf("action=lasso_lite_store_settings") === -1
			) {
				return;
			}
			var res;
			try {
				res = JSON.parse(xhr.responseText);
			} catch (err) {
				return;
			}
			if (!res || !res.success) {
				guard.pending_action = null;
				return;
			}
			guard.initial = getFormValues();
			if (guard.pending_action) {
				guard.ignore_before_unload = true;
				runPending();
				setTimeout(function () {
					guard.ignore_before_unload = false;
				}, 0);
			}
		});
	});
})(jQuery);
