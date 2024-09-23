var affiliateLasso = affiliateLasso || {};
var ajax_url = lassoLiteOptionsData.ajax_url;
var interval_time = 5000;
var initialFieldsValue = {};
var progessPercentage = 0;
var progressInterval = '';

jQuery(document).ready(function() {

	active_license_key();

	jQuery(document)
		.on('click', '#onboarding-save-display-btn', handleSettingSave);

	// A Function send the array of setting to ajax.php
	function handleSettingSave() {
		// Fetch all the settings
		var settings = lasso_lite_helper.fetchAllOptions();

		// Prepare data
		var data = {
			action: 'lasso_lite_store_settings',
			nonce: lassoLiteOptionsData.optionsNonce,
			settings: settings,
		};

		// Send the POST request
		jQuery.post(ajaxurl, data, function (response) {
			console.log('response', response);
		});

		go_to_next_step_action(this);
	}
});

// Check license-If success move to Amazon tab.
function active_license_key(){
	jQuery('#activate-license').click(function(event){
		event.preventDefault ? event.preventDefault() : (event.returnValue = false);
		// The loading screen
		showProgressBar("#license-activation-modal");
		var license = jQuery('[name = license_serial]').val();
		var settings = lasso_lite_helper.fetchAllOptions();
		let current_page = lasso_lite_helper.get_page_name();
		jQuery.ajax({
			url:ajax_url,
			type:'post',
			data : {
				action: 'lasso_lite_activate_license',
				security: lassoLiteOptionsData.optionsNonce,
				license: license,
				settings: settings,
				onboarding: current_page === 'install',
			},
			beforeSend:function(xhr){
				if(jQuery('[name = license_serial]').val() == ""){
					jQuery("#activate-error label").html('Please enter your license key.');
					jQuery("#activate-error").collapse();
					jQuery("#license").addClass("red-border");
					xhr.abort();

					// Track user enter license key Event
					lasso_segment_tracking('Lasso User Enters License Key', {
						license: license
					});
				} else {
					jQuery("#license-activation-modal").modal();
				}
			}
		})
		.done(function(response) {
			response = response.data;

			if (typeof response === 'undefined') {
				return;
			}

			if(response.status) {
				jQuery("#license-activation-progress").attr('aria-valuenow', 100).css('width', '100%');
				setTimeout(function () {
					jQuery("#license-activation-modal").modal('hide');
					jQuery("#onboarding_container").removeClass("container-sm");
					jQuery('#onboarding_container .tab-item').addClass('d-none');
					jQuery('#onboarding_container div[data-step="theme"]').removeClass('d-none');
				}, 800);

				window.location.href = "/wp-admin/edit.php?post_type=surl&page=surl-dashboard";

				// Track license key validate successful Event
				lasso_segment_tracking('Lasso License Key Validated', {
					license: license
				});

			} else {
				jQuery("#license-activation-modal").modal('hide');
				jQuery("#activate-error label").html(response.error_message);
				jQuery("#activate-error").collapse();
				jQuery("#license").addClass("red-border");

				if (typeof APP_ID !== 'undefined' && APP_ID !== 'string' && typeof lassoLiteOptionsData !== 'undefined' ) {
					APP_ID = lassoLiteOptionsData.app_id;
				}

				if (Intercom && intercomParams && response.hash != '') {
					intercomParams['user_hash'] = response.hash;
					window.intercomSettings = intercomParams;
					(function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',w.intercomSettings);}else{var d=document;var i=function(){i.c(arguments);};i.q=[];i.c=function(args){i.q.push(args);};w.Intercom=i;var l=function(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/' + APP_ID;var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);};if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})();
				}

				// Track license key validate fail Event
				lasso_segment_tracking('Lasso License Key Unvalidated', {
					license: license
				});
			}
		})
		.fail(function(xhr, msg) {
		})
		.always(function () {
			hideProgressBar("#license-activation-modal");
		});
	});
}	

/* SEGMENT ANALYTIC */
function lasso_segment_tracking( tracking_title, tracking_data = {} ) {
	try {
		if ( typeof analytics !== 'undefined') {
			analytics.track( tracking_title, tracking_data );
		}
	} catch (e) {
		console.log( e );
	}
}

function showProgressBar(selector){
	lasso_lite_helper.setProgressZero('.modal');
	lasso_lite_helper.scrollTop();
	
	jQuery(selector).modal('show');
	progressInterval = setInterval(function(){
		progress();
	}, 1000)
}

function hideProgressBar(selector){
	setProgressComplete();
	setTimeout(function(){
		jQuery(selector).modal('hide');
	}, 1000)
}

function setProgressComplete() {
	progessPercentage = 100;
	clearProgessInterval();
	jQuery(".modal").find(".progress-bar").css({width: progessPercentage + '%'});
}

function progress() {
	if(progessPercentage <=100) {
		progessPercentage += 25;
		jQuery(".modal").find(".progress-bar").css({width: progessPercentage + '%'});
	} else {
		clearProgessInterval();
	}
}

function clearProgessInterval() {
	clearInterval(progressInterval);
}
