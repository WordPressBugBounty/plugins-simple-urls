let tiny_mce_editor;
let lasso_editor_check = 0;
let lasso_model_html = '';
let tinymce_lasso_button_label = 'Add A Lasso Display';

if (typeof tinymce !== 'undefined') {
	tinymce.PluginManager.add('lasso_lite_tc_button', function(editor, url) {

		if(lasso_editor_check === 0) {
			tiny_mce_editor = editor;
			lasso_editor_check++;
		}

		let url_arr = url.split('/');
		url_arr.pop();
		let asset_url = url_arr.join('/');

		editor.addButton('lasso_lite_tc_button', {
			title: tinymce_lasso_button_label,
			image: asset_url + '/images/lasso-icon-tinymce.svg',
			icon: false,
			onclick: function() {
				let popup = jQuery('#lasso-display-add');
				if ( 0 === popup.length ) {
					jQuery('#wpcontent').append(lasso_model_html);
					init_lasso_modal();
					popup = jQuery('#lasso-display-add');
				}
				popup.modal('show');
			}
		});
	});
}

jQuery(function() {
	function loadPopupContent() {
		let allow_pages = ['content-links', 'keyword-opportunities'];
		let current_page = lasso_lite_helper.get_page_name();

		jQuery.ajax({
			url: lassoLiteOptionsData.ajax_url,
			type: 'get',
			data: {
				action: 'lasso_lite_get_display_html',
				nonce: lassoLiteOptionsData.optionsNonce,
			}
		})
			.done(function(res) {
				if (typeof res.data != 'undefined') {
					let data = res.data;
					lasso_model_html = data.html;

					jQuery('div[aria-label="' + tinymce_lasso_button_label + '"]').click(function() {
						let popup = jQuery('#lasso-display-add');
						if ( 0 === popup.length ) {
							jQuery('#wpcontent').append(lasso_model_html);
							init_lasso_modal();
							popup.modal('toggle');
						}
					});
				}

				if ( allow_pages.includes(current_page) ) {
					let popup = jQuery('#lasso-display-add');
					if ( 0 === popup.length ) {
						jQuery('#wpcontent').append(lasso_model_html);
						init_lasso_modal();
						popup.modal('toggle');
					}
				}

				// Fix for Divi Supreme Pro Editor
				if ( jQuery('#et_pb_layout').length != 0 && 0 === jQuery('#lasso-display-add').length ) {
					jQuery('#wpcontent').append(lasso_model_html);
					init_lasso_modal();
				}
			});
	}

	loadPopupContent();

});

function init_lasso_modal() {
    setTimeout(function () {
        init_lasso_display_add_modal();

        jQuery(document).on('ls.modal.shown', '#lasso-display-add', function () {
            reset_bs_transition_by_woody_code_plugin();
            jQuery('body').addClass('lasso-display-add-modal-open');
        });

        jQuery(document).on('ls.modal.hidden', '#lasso-display-add', function () {
            reset_bs_transition_by_woody_code_plugin('hide');
            jQuery('body').removeClass('lasso-display-add-modal-open');
        });

        jQuery(document).on('ls.modal.shown', '#url-add', function () {
            reset_bs_transition_by_woody_code_plugin();
        });

        jQuery(document).on('ls.modal.hidden', '#url-add', function () {
            reset_bs_transition_by_woody_code_plugin('hide');
        });

        jQuery(document).on('ls.modal.hidden', '#url-quick-detail', function () {
            reset_bs_transition_by_woody_code_plugin('hide');
        });
    }, 100);
}

function add_short_code_single(obj) {
	let link_slug = jQuery(obj).data('link-slug');
	let post_id = jQuery(obj).data('post-id');
	let shortcode = '[lasso ref="' + link_slug + '" id="' + post_id + '"]';

	try {
		tiny_mce_editor.insertContent(shortcode);
	} catch (e) {
		add_short_code_elementor(shortcode);
	}
	jQuery('#lasso-display-add').modal('hide');
}

jQuery(document).on('show.bs.modal', '#lasso-display-add', function () {
	jQuery('body').addClass('lasso-display-add-modal-open');
});
jQuery(document).on('hide.bs.modal', '#lasso-display-add', function () {
	jQuery('body').removeClass('lasso-display-add-modal-open');
});

function reset_bs_transition_by_woody_code_plugin(modal='show') {
    if ( lassoLiteOptionsData.is_wc_plugin_activate ) {
        jQuery.event.special.bsTransitionEnd = {
            bindType: 'transitionend',
            delegateType: 'transitionend'
        }

        if ( modal === 'hide' ) {
            setTimeout(function() {
                bindType = 'webkitTransitionEnd';
                delegateType = 'webkitTransitionEnd';
                jQuery.event.special.bsTransitionEnd = {
                    bindType: bindType,
                    delegateType: delegateType
                }
            }, 3000)
        }
    }
}
