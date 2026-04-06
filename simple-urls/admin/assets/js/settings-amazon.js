
jQuery(document).ready(function() {
	const AMAZON_API_MODE_STORAGE_KEY = 'lasso_lite_amazon_api_mode';
	const AMAZON_CREATORS_CUTOFF_AT = Date.parse('2026-05-01T00:00:00');

	jQuery(document)
		.on('click', '.btn-save-settings-amazon', save_setting_amazon)
		.on('change', '.amazon-api-mode-toggle', toggle_amazon_api_mode)
		.on('click', '.btn-verify-amazon-creators', verify_amazon_creators_credentials)
		.on('change', 'input[name="amazon_tracking_id"]', validate_tracking_id_format);

	function save_setting_amazon( event ) {
		event.preventDefault();
		lasso_lite_helper.setProgressZero();
		lasso_lite_helper.scrollTop();

		let amazon_tracking_id = jQuery('#amazon_tracking_id').val().trim();
		let amazon_access_key_id = jQuery('#amazon_access_key_id').val().trim();
		let amazon_secret_key = jQuery('#amazon_secret_key').val().trim();
		let amazon_creators_credential_id = jQuery('#amazon_creators_credential_id').val().trim();
		let amazon_creators_secret = jQuery('#amazon_creators_secret').val().trim();
		let amazon_creators_version = jQuery('#amazon_creators_version').val().trim();
		let amazon_creators_partner_tag = jQuery('#amazon_creators_partner_tag').val().trim();
		let amazon_default_tracking_country = jQuery('#amazon_default_tracking_country').val().trim();
		let amazon_pricing_daily = jQuery('#amazon_pricing_daily:checked').val();
		let auto_monetize_amazon = jQuery('#auto_monetize_amazon:checked').val();
		let auto_upgrade_eligible_links = jQuery('#auto_upgrade_eligible_links:checked').val();
		let btn_save = jQuery('.btn-save-settings-amazon');
		let is_tracking_id_valid = validate_tracking_id_format();
		let current_page = lasso_lite_helper.get_page_name();

		if ( is_tracking_id_valid ) {
			let lasso_lite_update_popup = jQuery('#url-save');
			lasso_lite_helper.add_loading_button( btn_save );
			jQuery.ajax({
				url: lassoLiteOptionsData.ajax_url,
				type: 'post',
				data: {
					action: 'lasso_lite_save_settings_amazon',
					nonce: lassoLiteOptionsData.optionsNonce,
					amazon_tracking_id: amazon_tracking_id,
					amazon_access_key_id: amazon_access_key_id,
					amazon_secret_key: amazon_secret_key,
					amazon_creators_credential_id: amazon_creators_credential_id,
					amazon_creators_secret: amazon_creators_secret,
					amazon_creators_version: amazon_creators_version,
					amazon_creators_partner_tag: amazon_creators_partner_tag,
					amazon_default_tracking_country: amazon_default_tracking_country,
					amazon_pricing_daily: amazon_pricing_daily,
					auto_monetize_amazon: auto_monetize_amazon,
					auto_upgrade_eligible_links: auto_upgrade_eligible_links,
				},
				beforeSend: function (xhr) {
					// Collapse current error + success notifications
					jQuery(".alert.red-bg.collapse").collapse('hide');
					jQuery(".alert.green-bg.collapse").collapse('hide');
					if ( 'surl-onboarding' !== current_page ) {
						lasso_lite_update_popup.modal('show');
						lasso_lite_helper.set_progress_bar( 98, 20 );
					}
				},
			})
				.done(function(res) {
					if ( res.success ) {
						lasso_lite_helper.do_notification(res.data.msg, 'green', 'default-template-notification-amz' );
						lasso_lite_helper.add_loading_button( btn_save, 'Save Changes', false );
					} else {
						lasso_lite_helper.do_notification("Unexpected error!", 'red', 'default-template-notification-amz' );
					}

					// Refresh setup process data
					refresh_setup_progress();
				})
				.always(function() {
					lasso_lite_helper.set_progress_bar_complete();
					setTimeout(function() {
						// Hide update popup by setTimeout to make sure this run after lasso_update_popup.modal('show')
						if ( 'surl-onboarding' !== current_page ) {
							lasso_lite_update_popup.modal('hide');
						}
					}, 1000);
				});

			// Go to next step if we are in Welcome page
			if ( 'surl-onboarding' === current_page ) {
				go_to_next_step_action(btn_save);
			}
		}
	}

	function toggle_amazon_api_mode( event ) {
		let modeToggle = jQuery(event.currentTarget);
		let nextMode = modeToggle.is(':checked') ? 'creators' : 'paapi';

		apply_amazon_api_mode(nextMode);
		window.localStorage.setItem(AMAZON_API_MODE_STORAGE_KEY, JSON.stringify({
			mode: nextMode,
			savedAt: new Date().toISOString(),
		}));
	}

	function apply_amazon_api_mode( mode ) {
		jQuery('.amazon-api-card').each(function() {
			let apiCard = jQuery(this);
			let paapiFields = apiCard.find('.amazon-paapi-fields');
			let creatorsFields = apiCard.find('.amazon-creators-fields');
			let modeToggle = apiCard.find('.amazon-api-mode-toggle');
			let isCreatorsMode = 'creators' === mode;

			paapiFields.toggleClass('d-none', isCreatorsMode);
			creatorsFields.toggleClass('d-none', ! isCreatorsMode);
			modeToggle.prop('checked', isCreatorsMode);
		});
	}

	function get_default_amazon_api_mode() {
		return Date.now() >= AMAZON_CREATORS_CUTOFF_AT ? 'creators' : 'paapi';
	}

	function get_saved_amazon_api_mode() {
		let storedValue = window.localStorage.getItem(AMAZON_API_MODE_STORAGE_KEY);
		let defaultMode = get_default_amazon_api_mode();

		if ( ! storedValue ) {
			return defaultMode;
		}

		try {
			let parsedValue = JSON.parse(storedValue);
			if ( parsedValue && parsedValue.mode ) {
				let savedAt = parsedValue.savedAt ? Date.parse(parsedValue.savedAt) : 0;
				if ( Date.now() >= AMAZON_CREATORS_CUTOFF_AT && savedAt < AMAZON_CREATORS_CUTOFF_AT && parsedValue.mode === 'paapi' ) {
					return 'creators';
				}

				return parsedValue.mode;
			}
		} catch (e) {
			if ( Date.now() >= AMAZON_CREATORS_CUTOFF_AT && storedValue === 'paapi' ) {
				return 'creators';
			}

			if ( storedValue === 'paapi' || storedValue === 'creators' ) {
				return storedValue;
			}
		}

		return defaultMode;
	}

	function verify_amazon_creators_credentials( event ) {
		event.preventDefault();

		let credentialId = jQuery('#amazon_creators_credential_id').val().trim();
		let secret = jQuery('#amazon_creators_secret').val().trim();
		let version = jQuery('#amazon_creators_version').val().trim();
		let partnerTag = jQuery('#amazon_creators_partner_tag').val().trim();
		let country = jQuery('#amazon_default_tracking_country').val().trim();
		let btnVerify = jQuery(event.currentTarget);
		let originalLabel = btnVerify.text().trim();

		lasso_lite_helper.clear_notifications();
		lasso_lite_helper.add_loading_button(btnVerify, originalLabel);

		jQuery.ajax({
			url: lassoLiteOptionsData.ajax_url,
			type: 'post',
			data: {
				action: 'lasso_lite_verify_amazon_creators_credentials',
				nonce: lassoLiteOptionsData.optionsNonce,
				amazon_creators_credential_id: credentialId,
				amazon_creators_secret: secret,
				amazon_creators_version: version,
				amazon_creators_partner_tag: partnerTag,
				amazon_default_tracking_country: country,
			},
		})
			.done(function(res) {
				if ( res.success ) {
					lasso_lite_helper.do_notification(res.data.msg, 'green', 'default-template-notification-amz');
				} else {
					let errorMsg = res.data && res.data.msg ? res.data.msg : 'Unable to verify Creators API credentials.';
					lasso_lite_helper.do_notification(errorMsg, 'red', 'default-template-notification-amz');
				}
			})
			.fail(function(xhr) {
				let errorMsg = lasso_lite_helper.get_msg_ajax_error(xhr);
				lasso_lite_helper.do_notification(errorMsg, 'red', 'default-template-notification-amz');
			})
			.always(function() {
				lasso_lite_helper.add_loading_button(btnVerify, originalLabel, false);
			});
	}

	/**
	 * Validate tracking id format if having the value
	 *
	 * @returns {boolean}
	 */
	function validate_tracking_id_format() {
		let is_valid = true;
		let trackingIdInput = jQuery('input[name="amazon_tracking_id"]');
		let trackingId = trackingIdInput.val() || '';
		let trackingIdInvalidMsg = jQuery('#tracking-id-invalid-msg');

		if ( trackingId !== '' ) {
			let re = new RegExp(lassoLiteOptionsData.amazon_tracking_id_regex, "i");
			is_valid = trackingId.match(re);
		}

		if ( is_valid ) {
			trackingIdInput.removeClass('invalid-field');
			trackingIdInvalidMsg.addClass('d-none');
		} else {
			trackingIdInput.addClass('invalid-field');
			trackingIdInvalidMsg.removeClass('d-none');
			jQuery('html, body').animate({
				scrollTop: jQuery('input[name="amazon_tracking_id"]').offset().top - 80
			}, 100);
		}

		return is_valid;
	}

	function validate_tracking_id_when_enable_auto_monetize() {
		let autoMonetize = jQuery('input[name="auto_monetize_amazon"]');
		let amzTrackingIdWhitelist = jQuery('input[name="amazon_multiple_tracking_id"]');
		let isChecked = autoMonetize.is(":checked");
		let trackingId = jQuery('input[name="amazon_tracking_id"]').val() || '';
		let amazonError = jQuery('.amazon-error');

		trackingId = trackingId.trim();
		if(isChecked) {
			if(trackingId === '') {
				let errorMessage = 'Tracking ID must be set to use Auto-Amazon.';
				amazonError.text(errorMessage);
				autoMonetize.prop('checked', false);
			} else {
				if (! amzTrackingIdWhitelist.is(":checked") ) {
					amzTrackingIdWhitelist.attr('data-old-checked', false);
					amzTrackingIdWhitelist.trigger('click');
				} else {
					amzTrackingIdWhitelist.attr('data-old-checked', true);
				}
				amazonError.text('');
			}
		}

		if(trackingId !== '') {
			amazonError.text('');
			autoMonetize.prop('disabled', false);
		}
	}

	function auto_monetize_amazon_links() {
		var autoMonetize = jQuery('input[name="auto_monetize_amazon"]');

		autoMonetize
			.change(validate_tracking_id_when_enable_auto_monetize)
			.change(function() {
				let autoMonetize = jQuery(this);
				var isChecked = autoMonetize.is(":checked");
				if(isChecked && license_active === '0') {
					jQuery("#enable-support").modal('show');
				}
			});

		jQuery('input[name="amazon_tracking_id"]')
			.change(validate_tracking_id_when_enable_auto_monetize)
			.change(validate_tracking_id_format);
	}

	auto_monetize_amazon_links();
	apply_amazon_api_mode(get_saved_amazon_api_mode());
});
