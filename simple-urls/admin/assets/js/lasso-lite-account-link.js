jQuery(document).ready(function() {
	let lite_cta_signup_inner_html_backup = '';

	function lite_account_link_notification_template() {
		if ( lassoLiteOptionsData && lassoLiteOptionsData.lite_account_link_notification_template ) {
			return lassoLiteOptionsData.lite_account_link_notification_template;
		}
		return 'default-template-notification';
	}

	function lite_account_link_notify( msg, color ) {
		lasso_lite_helper.do_notification(
			msg || 'Your Lasso account is now linked for this site.',
			color || 'green',
			lite_account_link_notification_template()
		);
	}

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
		.on('click', '.lasso-lite-validate-lite-account', function(event) {
			event.preventDefault();
			event.stopPropagation();
			jQuery('#lasso-lite-account-existing-modal').data('liteModalMode', 'validate');
			jQuery('#lasso-lite-account-existing-modal').modal('show');
		})
		.on('click', '.lasso-lite-open-existing-account-login', function(event) {
			event.preventDefault();
			jQuery('#lasso-lite-analytics-modal').modal('hide');
			jQuery('#lasso-lite-account-existing-modal').data('liteModalMode', 'login');
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
						lite_account_link_notify(d.msg, 'green');
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
						let $modalInner = jQuery('#lasso-lite-account-existing-modal');
						let from_validate = 'validate' === $modalInner.data('liteModalMode');
						if ( from_validate ) {
							$modalInner.data('litePostSignupCreatorsVerify', true);
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
						lite_account_link_notify(d.msg, 'green');
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
});
