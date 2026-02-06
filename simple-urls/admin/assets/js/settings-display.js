var lasso_lite_open_need_more_customization = 'lasso_lite_open_need_more_customization';

jQuery(document).ready(function() {
	customize_displays_handler();
	lock_brag_mode_on();
	onboarding_set_customizations();

	jQuery(document)
		.on('click', '#btn-save-settings-display', handle_display_setting_save)
		.on('click', '#need-more-customization', need_more_customization);

	jQuery(".lasso-lite-admin-settings-form").submit(function(e) {
		e.preventDefault();
	});

	jQuery('#show_disclosure').on('change', function() {
		let disclosure = jQuery('.lasso-disclosure');
		if(jQuery(this).prop('checked') == true) {
			disclosure.css('visibility','visible');
			disclosure.css('display','block');
		} else {
			disclosure.css('visibility', 'hidden');
			disclosure.css('display', 'none');
		}
	});

	jQuery('#show_price').on('change', function() {
		let price = jQuery('.lasso-price');
		if(jQuery(this).prop('checked') == true) {
			price.css('visibility','visible');
			price.css('display','block');
		} else {
			price.css('visibility', 'hidden');
			price.css('display', 'none');
		}
	});

	jQuery('#disclosure_text').on('keyup', function(){
		let text = jQuery(this).val();
		jQuery('.lasso-disclosure').text(text);
	});

	jQuery('.color-picker').spectrum({
		type: "component",
		hideAfterPaletteSelect: "true",
		showAlpha: "false",
		allowEmpty: "false"
	});

	// Auto open customization display helper after enabling support
	if ( lassoLiteOptionsData.setup_progress.enable_support && localStorage.getItem( lasso_lite_open_need_more_customization ) ) {
		localStorage.removeItem( lasso_lite_open_need_more_customization );

		// Waiting for a while then run Intercom function
		setTimeout(function () {
			window.Intercom('showNewMessage', 'Hey team, can you help with special customizations to the Lasso Displays? I need...');
		}, 200);
	}
});

// A Function send the array of setting to ajax.php
function handle_display_setting_save() {
	lasso_lite_helper.setProgressZero();
	lasso_lite_helper.scrollTop();

	// Fetch all the settings
	let settings                = lasso_lite_helper.fetchAllOptions();
	let btn_save                = jQuery('#btn-save-settings-display');
	let lasso_lite_update_popup = jQuery('#url-save');

	// Prepare data
	let data = {
		action: 'lasso_lite_store_settings',
		nonce: lassoLiteOptionsData.optionsNonce,
		settings: settings,
	};

	// Send the POST request
	lasso_lite_helper.add_loading_button( btn_save );
	jQuery.ajax({
		url: lassoLiteOptionsData.ajax_url,
		type: 'post',
		data: data,
		beforeSend: function (xhr) {
			// Collapse current error + success notifications
			jQuery(".alert.red-bg.collapse").collapse('hide');
			jQuery(".alert.green-bg.collapse").collapse('hide');
			lasso_lite_update_popup.modal('show');
			lasso_lite_helper.set_progress_bar( 98, 20 );
		},
	})
	.done(function(res) {
		if ( res.success ) {
			lasso_lite_helper.do_notification('All settings saved', 'green', 'default-template-notification' );
			lasso_lite_helper.add_loading_button( btn_save, 'Save Changes', false );
		} else {
			lasso_lite_helper.do_notification("Unexpected error!", 'red', 'default-template-notification' );
		}

		// Refresh setup process data
		refresh_setup_progress();
	})
	.always(function() {
		lasso_lite_helper.set_progress_bar_complete();
		setTimeout(function() {
			// Hide update popup by setTimeout to make sure this run after lasso_update_popup.modal('show')
			lasso_lite_update_popup.modal('hide');
		}, 1000);
	});
}

function customize_displays_handler() {
	jQuery("[name='display_color_main']").unbind().change(function() {
		onboarding_set_customizations();
	});
	jQuery("[name='display_color_title']").unbind().change(function() {
		onboarding_set_customizations();
	});
	jQuery("[name='display_color_background']").unbind().change(function() {
		onboarding_set_customizations();
	});
	jQuery("[name='display_color_button']").unbind().change(function() {
		onboarding_set_customizations();
	});
	jQuery("[name='display_color_secondary_button']").unbind().change(function() {
		onboarding_set_customizations();
	});
	jQuery("[name='display_color_pros']").unbind().change(function() {
		onboarding_set_customizations();
	});
	jQuery("[name='display_color_cons']").unbind().change(function() {
		onboarding_set_customizations();
	});
	jQuery("[name='display_color_button_text']").unbind().change(function() {
		onboarding_set_customizations();
	});
	jQuery("[name='primary_button_text']").unbind().keyup(function() {
		onboarding_set_customizations();
	});
	jQuery("[name='secondary_button_text']").unbind().keyup(function() {
		onboarding_set_customizations();
	});
	jQuery("[name='disclosure_text']").unbind().keyup(function() {
		onboarding_set_customizations();
	});
	jQuery("#show_disclosure").off("change").on("change", function(event) {
		onboarding_set_customizations();
	});
	jQuery("#show_price").off("change").on("change", function(event) {
		onboarding_set_customizations();
	});
}

function lock_brag_mode_on() {
	let brag_mode_toggle = jQuery("[name='enable_brag_mode']");
	if ( brag_mode_toggle.length === 0 ) {
		return;
	}

	brag_mode_toggle.prop("checked", true);

	brag_mode_toggle
		.off("click.lassoLiteBragLock")
		.on("click.lassoLiteBragLock", function(event) {
			event.preventDefault();
			event.stopPropagation();

			jQuery(this).prop("checked", true);
			onboarding_set_customizations();

			let upgrade_url = window.lassoLiteOptionsData.upgrade_url;

			if ( upgrade_url ) {
				window.open(upgrade_url, "_blank");
			}
			return false;
		});
}

function onboarding_set_customizations() {
	let box_style = `
			--lasso-main: `+jQuery("[name='display_color_main']").val()+'!important;'+`;
			--lasso-title: `+jQuery("[name='display_color_title']").val()+'!important;'+`;
			--lasso-background: `+jQuery("[name='display_color_background']").val()+'!important;'+`;
			--lasso-button: `+jQuery("[name='display_color_button']").val()+'!important;'+`;
			--lasso-secondary-button: `+jQuery("[name='display_color_secondary_button']").val()+'!important;'+`;
			--lasso-button-text: `+jQuery("[name='display_color_button_text']").val()+'!important;'+`;
			--lasso-pros: `+jQuery("[name='display_color_pros']").val()+'!important;'+`;
			--lasso-cons: `+jQuery("[name='display_color_cons']").val()+'!important;'+`;
		`;
	jQuery(":root").attr('style', box_style);

	jQuery(".lasso-button-1").html(jQuery("[name='primary_button_text']").val());
	jQuery(".lasso-button-2").html(jQuery("[name='secondary_button_text']").val());
	jQuery(".lasso-disclosure").html(jQuery("[name='disclosure_text']").val());

	let disclosure_txt = jQuery(".lasso-disclosure");
	if(jQuery('#show_disclosure').is(":checked")) {
		disclosure_txt.removeClass("d-none");
	} else {
		disclosure_txt.addClass("d-none");
	}

	let price_txt = jQuery(".lasso-price");
	let price_date = jQuery(".lasso-date");
	if(jQuery('#show_price').is(":checked")) {
		price_txt.removeClass("d-none");
		price_date.removeClass("d-none");
	} else {
		price_txt.addClass("d-none");
		price_date.addClass("d-none");
	}

	let brag_icon = jQuery(".lasso-brag");
	brag_icon.removeClass("d-none");
}

function need_more_customization() {
	if ( lassoLiteOptionsData.setup_progress.enable_support ) {
		window.Intercom('showNewMessage', 'Hey team, can you help with special customizations to the Lasso Displays? I need...');
	} else {
		window.need_more_customization_flag = 1;
		jQuery('#enable-support').modal('show');
	}
}