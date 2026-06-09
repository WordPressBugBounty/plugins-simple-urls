
jQuery(document).ready(function() {
	const AMAZON_API_MODE_STORAGE_KEY = 'lasso_lite_amazon_api_mode';
	const AMAZON_CREATORS_CUTOFF_AT = Date.parse('2026-05-01T00:00:00');
	let lite_cta_signup_inner_html_backup = '';
	let amazonApiModeApplying = false;

	function capture_lite_cta_signup_inner_backup() {
		let inner = document.getElementById('lasso-lite-cta-signup-inner');
		if (inner && !lite_cta_signup_inner_html_backup) {
			lite_cta_signup_inner_html_backup = inner.innerHTML;
		}
	}

	function restore_lite_cta_signup_inner_if_needed() {
		let inner = jQuery('#lasso-lite-cta-signup-inner');
		if (!inner.length || !lite_cta_signup_inner_html_backup) {
			return;
		}
		if (!inner.find('#lasso-lite-cta-btn-google-signup').length) {
			inner.html(lite_cta_signup_inner_html_backup);
		}
	}

	function apply_lite_account_modal_copy(mode) {
		let is_validate = 'validate' === mode;
		let title = jQuery('#lasso-lite-account-existing-modal-label');
		let submit = jQuery('#lasso-lite-cta-login-submit');
		let intro = jQuery('#lasso-lite-cta-login-intro');
		if (title.length) {
			title.text(is_validate ? title.data('copyValidate') : title.data('copyLogin'));
		}
		if (submit.length) {
			submit.text(is_validate ? submit.data('copyValidate') : submit.data('copyLogin'));
		}
		if (intro.length) {
			if (is_validate && intro.data('validateIntro')) {
				intro.text(intro.data('validateIntro'));
			} else if (intro.data('defaultIntro')) {
				intro.text(intro.data('defaultIntro'));
			}
		}
	}

	function reset_lite_account_modal_panels() {
		jQuery('#lasso-lite-cta-login-panel').removeClass('d-none');
		jQuery('#lasso-lite-cta-signup-panel').addClass('d-none');
		jQuery('#lasso-lite-cta-login-error').addClass('d-none').text('');
		jQuery('#lasso-lite-cta-login-password').val('');
		jQuery('#lasso-lite-cta-login-password-group').addClass('d-none');
		restore_lite_cta_signup_inner_if_needed();
		let wrap = jQuery('#lasso-lite-cta-signup-wrapper');
		wrap.find('#lasso-lite-cta-email-signup-form').addClass('d-none');
		wrap.find('#lasso-lite-cta-btn-email-signup-toggle').removeClass('d-none');
		jQuery('#lasso-lite-cta-general-error, #lasso-lite-cta-email-error, #lasso-lite-cta-password-error').addClass('d-none').text('');
		jQuery('#lasso-lite-cta-signup-email').val(jQuery('#lasso-lite-cta-login-email').val() || '');
		jQuery('#lasso-lite-cta-signup-password').val('');
		jQuery('#lasso-lite-account-existing-modal').removeData('liteAwaitingSync');
		if (window.lassoLiteSignupResetSuccessGuard) {
			window.lassoLiteSignupResetSuccessGuard();
		}
	}

	jQuery(function() {
		capture_lite_cta_signup_inner_backup();
	});

	jQuery('#lasso-lite-account-existing-modal')
		.on('show.bs.modal', function() {
			jQuery(this).removeData('litePendingCreatorsVerifyAfterSignup');
			reset_lite_account_modal_panels();
			let mode = jQuery(this).data('liteModalMode') || 'login';
			apply_lite_account_modal_copy(mode);
		})
		.on('hidden.bs.modal', function() {
			let $m = jQuery(this);
			let t = $m.data('litePostSignupVerifyTimeout');
			if ( t ) {
				clearTimeout( t );
				$m.removeData('litePostSignupVerifyTimeout');
			}
			if ( $m.data('litePendingCreatorsVerifyAfterSignup') ) {
				$m.removeData('litePendingCreatorsVerifyAfterSignup');
				if ( window.lassoLiteMaybeTriggerCreatorsVerifyAfterLink ) {
					window.lassoLiteMaybeTriggerCreatorsVerifyAfterLink();
				}
			}
			$m.removeData('liteModalMode');
			$m.removeData('litePostSignupCreatorsVerify');
			$m.removeData('liteAwaitingSync');
		});

	jQuery(document)
		.on('click', '.btn-save-settings-amazon', save_setting_amazon)
		.on('click', '.btn-lasso-lite-verify-amazon-creators', verify_amazon_creators_credentials)
		.on('change', 'input[name="amazon_tracking_id"]', validate_tracking_id_format)
		.on('click', '.lasso-lite-validate-lite-account', function(event) {
			event.preventDefault();
			event.stopPropagation();
			jQuery('#lasso-lite-account-existing-modal').data('liteModalMode', 'validate');
			jQuery('#lasso-lite-account-existing-modal').modal('show');
		})
		.on('click', '#lasso-lite-cta-show-signup', function(event) {
			event.preventDefault();
			jQuery('#lasso-lite-cta-login-panel').addClass('d-none');
			jQuery('#lasso-lite-cta-signup-panel').removeClass('d-none');
		})
		.on('click', '#lasso-lite-cta-show-login', function(event) {
			event.preventDefault();
			jQuery('#lasso-lite-account-existing-modal').removeData('litePostSignupCreatorsVerify');
			reset_lite_account_modal_panels();
			let mode = jQuery('#lasso-lite-account-existing-modal').data('liteModalMode') || 'login';
			apply_lite_account_modal_copy(mode);
		});

	function show_amazon_notification_html(message, color) {
		let alert_id = '_' + Math.random().toString(36).substr(2, 9);
		let alert_bg = color + '-bg';
		lasso_lite_helper.inject_to_template(
			jQuery('#lasso_lite_notifications'),
			'default-template-notification-amz-html',
			[
				{
					alert_id: alert_id,
					alert_bg: alert_bg,
					message: message,
				},
			],
			true
		);
		jQuery('#' + alert_id).collapse('show');
	}

	function amazon_notify_with_lite_account_cta(base_msg, color) {
		// Message only; CTA link lives in default-template-amz-html-jsrender.html (real <a>, not JsRender {{html:}}).
		show_amazon_notification_html(base_msg || '', color);
	}

	jQuery(document).on('click', '#lasso-lite-cta-toggle-login-password', function(event) {
		event.preventDefault();
		let btn = jQuery(this);
		let wrap = btn.closest('.lasso-password-wrapper');
		let input = wrap.find('input').first();
		let icons = btn.find('svg');
		if (input.attr('type') === 'password') {
			input.attr('type', 'text');
			icons.eq(0).addClass('d-none');
			icons.eq(1).removeClass('d-none');
		} else {
			input.attr('type', 'password');
			icons.eq(0).removeClass('d-none');
			icons.eq(1).addClass('d-none');
		}
	});

	function maybe_trigger_creators_verify_after_link() {
		let verifyBtn = jQuery('.btn-lasso-lite-verify-amazon-creators').first();
		if (verifyBtn.length) {
			setTimeout(function() {
				verifyBtn.trigger('click');
			}, 150);
		}
	}

	if ( typeof window !== 'undefined' ) {
		window.lassoLiteMaybeTriggerCreatorsVerifyAfterLink = maybe_trigger_creators_verify_after_link;
	}

	jQuery('#lasso-lite-cta-login-submit').on('click', function() {
		let $modal = jQuery('#lasso-lite-account-existing-modal');
		let email = jQuery('#lasso-lite-cta-login-email').val().trim();
		let err_el = jQuery('#lasso-lite-cta-login-error');
		let btn = jQuery(this);
		let original_label = btn.text().trim();
		let $pwdGroup = jQuery('#lasso-lite-cta-login-password-group');
		let intro = jQuery('#lasso-lite-cta-login-intro');

		err_el.addClass('d-none').text('');

		if (!email) {
			err_el.removeClass('d-none').text('Email is required.');
			return;
		}

		function run_sync_lite_account() {
			lasso_lite_helper.add_loading_button(btn, original_label);

			jQuery.ajax({
				url: lassoLiteOptionsData.ajax_url,
				type: 'post',
				data: {
					action: 'lasso_lite_sync_lite_account_via_existing_login',
					nonce: lassoLiteOptionsData.optionsNonce,
					email: email,
				},
			})
				.done(function(res) {
					if (!res.success) {
						let m = res.data && res.data.msg ? res.data.msg : 'Request failed.';
						err_el.removeClass('d-none').text(m);
						return;
					}
					let d = res.data || {};
					if (d.needs_signup) {
						let $m = jQuery('#lasso-lite-account-existing-modal');
						let from_validate = 'validate' === $m.data('liteModalMode');
						if (from_validate) {
							$m.data('litePostSignupCreatorsVerify', true);
						}
						err_el.addClass('d-none').text('');
						jQuery('#lasso-lite-cta-signup-email').val(email);
						jQuery('#lasso-lite-cta-login-panel').addClass('d-none');
						jQuery('#lasso-lite-cta-signup-panel').removeClass('d-none');
						return;
					}
					if (d.linked) {
						jQuery('#lasso-lite-account-existing-modal').modal('hide');
						jQuery('#lasso-lite-cta-login-password').val('');
						lasso_lite_helper.clear_notifications();
						lasso_lite_helper.do_notification(d.msg || 'Your Lasso account is now linked for this site.', 'green', 'default-template-notification-amz');
						maybe_trigger_creators_verify_after_link();
						return;
					}
					if (d.link_account_required) {
						$modal.data('liteAwaitingSync', true);
						if (intro.length && intro.data('step2Intro')) {
							intro.text(intro.data('step2Intro'));
						}
						btn.focus();
						return;
					}
					err_el.removeClass('d-none').text(d.msg || 'Unable to continue.');
				})
				.fail(function(xhr) {
					err_el.removeClass('d-none').text(lasso_lite_helper.get_msg_ajax_error(xhr));
				})
				.always(function() {
					lasso_lite_helper.add_loading_button(btn, original_label, false);
				});
		}

		if ($modal.data('liteAwaitingSync')) {
			$modal.removeData('liteAwaitingSync');
			run_sync_lite_account();
			return;
		}

		var lite_account_existing_always_post = lassoLiteOptionsData && lassoLiteOptionsData.lite_account_existing_always_post;

		if ($pwdGroup.hasClass('d-none') && !lite_account_existing_always_post) {
			lasso_lite_helper.add_loading_button(btn, original_label);
			jQuery.ajax({
				url: lassoLiteOptionsData.ajax_url,
				type: 'post',
				dataType: 'json',
				data: {
					action: 'lasso_lite_validate_lite_account_email',
					nonce: lassoLiteOptionsData.optionsNonce,
					email: email,
				},
			})
				.done(function(res) {
					if (!res.success) {
						let m = res.data && res.data.msg ? res.data.msg : 'Request failed.';
						err_el.removeClass('d-none').text(m);
						return;
					}
					let d = res.data || {};
					if (d.needs_signup) {
						let $modal = jQuery('#lasso-lite-account-existing-modal');
						let from_validate = 'validate' === $modal.data('liteModalMode');
						if ( from_validate ) {
							$modal.data('litePostSignupCreatorsVerify', true);
						}
						err_el.addClass('d-none').text('');
						jQuery('#lasso-lite-cta-signup-email').val(email);
						jQuery('#lasso-lite-cta-login-panel').addClass('d-none');
						jQuery('#lasso-lite-cta-signup-panel').removeClass('d-none');
						return;
					}
					if (d.linked) {
						jQuery('#lasso-lite-account-existing-modal').modal('hide');
						jQuery('#lasso-lite-cta-login-password').val('');
						lasso_lite_helper.clear_notifications();
						lasso_lite_helper.do_notification(d.msg || 'Your Lasso account is now linked for this site.', 'green', 'default-template-notification-amz');
						maybe_trigger_creators_verify_after_link();
						return;
					}
					if (d.link_account_required) {
						$modal.data('liteAwaitingSync', true);
						if (intro.length && intro.data('step2Intro')) {
							intro.text(intro.data('step2Intro'));
						}
						btn.focus();
						return;
					}
					err_el.removeClass('d-none').text(d.msg || 'Unable to continue.');
				})
				.fail(function(xhr) {
					err_el.removeClass('d-none').text(lasso_lite_helper.get_msg_ajax_error(xhr));
				})
				.always(function() {
					lasso_lite_helper.add_loading_button(btn, original_label, false);
				});
			return;
		}

		run_sync_lite_account();
	});

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
						if (
							res.data &&
							res.data.creators_validation_attempted &&
							false === res.data.creators_validation_success
						) {
							if (res.data.creators_validation_lite_account_cta) {
								amazon_notify_with_lite_account_cta(res.data.creators_validation_msg, 'orange');
							} else {
								lasso_lite_helper.do_notification(res.data.creators_validation_msg, 'orange', 'default-template-notification-amz' );
							}
						} else {
							lasso_lite_helper.do_notification(res.data.msg, 'green', 'default-template-notification-amz' );
						}
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

	function save_amazon_api_mode( nextMode ) {
		try {
			if ( typeof window !== 'undefined' && window.localStorage ) {
				window.localStorage.setItem( AMAZON_API_MODE_STORAGE_KEY, JSON.stringify( {
					mode: nextMode,
					savedAt: new Date().toISOString(),
				} ) );
			}
		} catch (e) {
			// Ignore storage failures and fall back to the default mode.
		}
	}

	function get_saved_amazon_api_mode_value() {
		try {
			if ( typeof window !== 'undefined' && window.localStorage ) {
				return window.localStorage.getItem( AMAZON_API_MODE_STORAGE_KEY );
			}
		} catch (e) {
			// Ignore storage failures and fall back to the default mode.
		}

		return null;
	}

	function toggle_amazon_api_mode( event ) {
		if ( amazonApiModeApplying ) {
			return;
		}
		let modeToggle = jQuery(event.currentTarget);
		let nextMode = modeToggle.is(':checked') ? 'creators' : 'paapi';

		apply_amazon_api_mode(nextMode);
		save_amazon_api_mode(nextMode);
	}

	function apply_amazon_api_mode( mode ) {
		amazonApiModeApplying = true;
		try {
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
		} finally {
			amazonApiModeApplying = false;
		}
	}

	function get_default_amazon_api_mode() {
		return Date.now() >= AMAZON_CREATORS_CUTOFF_AT ? 'creators' : 'paapi';
	}

	function get_saved_amazon_api_mode() {
		let storedValue = get_saved_amazon_api_mode_value();
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
		event.stopImmediatePropagation();

		let credentialId = jQuery('#amazon_creators_credential_id').val().trim();
		let secret = jQuery('#amazon_creators_secret').val().trim();
		let version = jQuery('#amazon_creators_version').val().trim();
		let partnerTag = jQuery('#amazon_creators_partner_tag').val().trim();
		let country = jQuery('#amazon_default_tracking_country').val().trim();
		let btnVerify = jQuery(event.currentTarget);
		let originalLabel = btnVerify.text().trim();

		// Immediate removal: clear_notifications() only collapse-hides (animated), so the Lite-account CTA banner can linger during verify.
		jQuery('#lasso_lite_notifications').empty();
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
					if (res.data && res.data.lite_account_validate_cta) {
						amazon_notify_with_lite_account_cta(errorMsg, 'red');
					} else {
						lasso_lite_helper.do_notification(errorMsg, 'red', 'default-template-notification-amz');
					}
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
	jQuery(document).off('change.lassoLiteAmazonApi', '.amazon-api-mode-toggle').on('change.lassoLiteAmazonApi', '.amazon-api-mode-toggle', toggle_amazon_api_mode);
	apply_amazon_api_mode(get_saved_amazon_api_mode());
});
