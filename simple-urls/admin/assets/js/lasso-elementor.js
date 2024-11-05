let window_url_detail;
let view_elementor;
let model_elementor;
let el_lasso_shortcode_textarea        = '.elementor-control-lasso_shortcode.elementor-control-type-textarea .elementor-control-tag-area';
let shortcodes_reload                  = [];
let customizing_display                = (typeof lassoLiteOptionsData !== 'undefined' && lassoLiteOptionsData.customizing_display) || [];
let toogle_attributes                  = customizing_display['toogle_attributes'] || [];
let default_lasso_shortcode_attributes = ['ref', 'id', 'link_id', 'type', 'category'];
let modal_display_popup_html           = '';
let allow_pages                        = [];
let current_page                       = 'elementor';
let loading_html                       = '<div class="py-5"><div class="loader"></div></div>';

jQuery( function( $ ) {
	function loadPopupContent() {
		$.ajax({
			url: lassoLiteOptionsData.ajax_url,
			type: 'get',
			data: {
				action: 'lasso_lite_get_display_html',
				nonce: lassoLiteOptionsData.optionsNonce,
			}
		})
			.done(function(res) {
				res = res.data;
				modal_display_popup_html = res.html;
			});
	}

	loadPopupContent();

	// Define throttle
	function throttle(func, limit) {
		let lastFunc;
		let lastRan;
		return function(...args) {
			const context = this;
			if (!lastRan) {
				func.apply(context, args);
				lastRan = Date.now();
			} else {
				clearTimeout(lastFunc);
				lastFunc = setTimeout(function() {
					if ((Date.now() - lastRan) >= limit) {
						func.apply(context, args);
						lastRan = Date.now();
					}
				}, limit - (Date.now() - lastRan));
			}
		};
	}

	if ( window.elementorFrontend && elementorFrontend.isEditMode() ) {
		// Use throttle for getLassoShortcodeHtml
		const throttledGetLassoShortcodeHtml = throttle(function(id, shortcode) {
			getLassoShortcodeHtml(id, shortcode, false);
		}, 2000); // Change to throttle time you want


		// ? Load Lasso shortcode on editable mode
		elementorFrontend.hooks.addAction( 'frontend/element_ready/lasso_shortcode.default', function( $scope ) {
			let id = $scope.data('id');

			let shortcode = parent.jQuery(el_lasso_shortcode_textarea).val();
			if (shortcode) {
				jQuery('.elementor-element-' + id).find('div.shortcode-html').html(loading_html);
				throttledGetLassoShortcodeHtml(id, shortcode);
			}
		} );

		elementor.hooks.addAction( 'panel/open_editor/widget/lasso_shortcode', function( panel, model, view ) {
			let content_tab_el = '.elementor-panel-navigation .elementor-tab-control-content';
			view_elementor     = view;
			model_elementor    = model;

			init_editor_fields( view, model )

			// ? Correctly display fields editor change tab
			parent.jQuery(parent.document).on('click', content_tab_el, function (e){
				init_editor_fields( view, model );
			});

			view.renderOnChange();
		});
	}
});

jQuery(document).ready(function () {
	jQuery(document).on('click', '.elementor-element-edit-mode.elementor-widget-lasso_shortcode .elementor-editor-widget-settings', function(){
		view_elementor.renderOnChange();
	});

	jQuery(document).on('click', '.btn-modal-add-display', function(){
		let is_lasso_modal_exist = true;
		let widget_container = jQuery(this).closest('.elementor-widget-lasso_shortcode');
		if ( ! jQuery(widget_container).hasClass('lasso-modal') ) {
			is_lasso_modal_exist = false;
		}

		// Init Lasso display modal and open it
		setTimeout(function (){
			if ( ! is_lasso_modal_exist ) {
				jQuery(widget_container).append(modal_display_popup_html);
				init_lasso_display_add_modal({
					"destroyEventsAfterClose": true
				});
			}
			lasso_segment_tracking('Open "Choose a Display Type" Popup');

			let main_nav = jQuery('div.elementor-widget-creativo_navigation_menu').closest('section.elementor-section');
			if ( main_nav.length ) {
				window.lasso_display_add_modal.onModalHidden( function () {
					main_nav.show();
				});
				window.lasso_display_add_modal.onModalHidden( function () {
					main_nav.hide();
				});
			}

			window.lasso_display_add_modal.open();
			window.lasso_display_add_modal.onModalShow(function(){
				console.log('Modal is shown');
			});
		}, 50);
	});

	jQuery(document).on('click', '.lasso-update-display', function (){
		if ( parent.jQuery(el_lasso_shortcode_textarea).length > 0 ) {
			// ? Update input lasso shortcode on change
			let id        = jQuery(this).closest('[data-element_type]').data('id');
			let shortcode = parent.jQuery(el_lasso_shortcode_textarea).val();

			if ( shortcode ) {
				getLassoShortcodeHtml(id, shortcode, false);
			}
		}
	});

	jQuery(document).on('click', '.lasso-edit-display', function (){
		if ( parent.jQuery(el_lasso_shortcode_textarea).length > 0 ) {
			// ? Update input lasso shortcode on change
			let id        = jQuery(this).closest('[data-element_type]').data('id');
			let shortcode = parent.jQuery(el_lasso_shortcode_textarea).val();

			if ( shortcode ) {
				open_url_detail_window( id, shortcode )
			}
		}
	});

	jQuery(document).on('keyup', '.shortcode-input', function (e){
		if ( parent.jQuery(el_lasso_shortcode_textarea).length > 0 ) {
			// ? Update input lasso shortcode on change
			parent.jQuery(el_lasso_shortcode_textarea).val(jQuery(this).val());
			model_elementor.attributes.settings.attributes.lasso_shortcode = jQuery(this).val();
		}
	});

	jQuery(document).on('focusout', '.shortcode-input', function (e){
		if ( parent.jQuery(el_lasso_shortcode_textarea).length > 0 ) {
			model_elementor.renderRemoteServer();
		}
	});

	jQuery(document).on('click', '.ql-editor', function(){
		if ( window.quill ) {
			window.quill.focus();
		}
	});

});

function getLassoShortcodeHtml(id, shortcode, is_new_display) {
	jQuery.ajax({
		url: lassoLiteOptionsData.ajax_url,
		type: 'get',
		data: {
			action: 'lasso_lite_get_shortcode_content',
			nonce: lassoLiteOptionsData.optionsNonce,
			shortcode: shortcode,
		},
		beforeSend: function( xhr ) {
			jQuery('.elementor-element-' + id).find('div.shortcode-html').html(loading_html);
		}
	})
		.done(function(res) {
			res = res.data;
			html = res.html;
			jQuery('.elementor-element-' + id).find('div.shortcode-html').html(html);

			// Tracking if Display Added
			if ( is_new_display ) {
				lasso_segment_tracking('Display Added', {
					shortcode: shortcode
				});
			}
		})
		.always(function() {
			jQuery('.elementor-element-' + id).find('div.py-5').remove();
		});
}

function add_short_code_elementor(shortcode) {
	// ? Set lasso shortcode to input
	parent.jQuery(el_lasso_shortcode_textarea).val(shortcode).trigger('input');

	clean_modal();
	trigger_load_preview();
}

function clean_modal(){
	window.lasso_display_add_modal.close();
}

function trigger_load_preview(){
	model_elementor.renderRemoteServer();
}

function open_url_detail_window(id, shortcode) {
	if( typeof window_url_detail === 'object' ) {
		window_url_detail.close();  // close windows are opening
	}

	let current_attributes = get_lasso_shortcode_attributes(shortcode);
	let display_type       = current_attributes['type'] ? current_attributes['type'] : 'single';
	let detail_page        = '';
	let post_id            = 0;

	if ( current_attributes.hasOwnProperty('id') ) {
		post_id = current_attributes.id;
		detail_page = lassoLiteOptionsData.site_url + "/wp-admin/edit.php?post_type=" + lassoLiteOptionsData.simple_urls_slug + "&page=" + lassoLiteOptionsData.page_url_details +  "&post_id=" + post_id;
	}

	if ( post_id !== 0 && ! isNaN(post_id) ) {
		shortcodes_reload.push({blockId: id, shortcode: shortcode});
		window_url_detail = window.open(detail_page,'_blank');
		window_url_detail.onload = function(){
			this.onbeforeunload = function(){
				for ( let i = 0; i < shortcodes_reload.length; i++ ) {
					getLassoShortcodeHtml(id, shortcodes_reload[i].shortcode);
				}
				shortcodes_reload = [];
			}
		}
	}
}

function get_lasso_shortcode_attributes( shortcode ) {
	var result = {};

	try {
		var raw_attributes = shortcode.replace(/\[lasso/g, '').replace(/\]/g, '').trim();
		var temporary_element = '<div ' + raw_attributes + '></div>';
		temporary_element = jQuery(temporary_element);

		jQuery(temporary_element).each(function() {
			jQuery.each(this.attributes, function() {
				if(this.specified) {
					result[this.name] = this.value;
				}
			});
		});
	} catch (e) {}

	return result;
}

function customize_shortcode( cus_attr_name, cus_attr_value ) {
	let shortcode = parent.jQuery(el_lasso_shortcode_textarea).val();

	if (shortcode && shortcode.match(/\[lasso.*\]/)) {
		var current_attributes = get_lasso_shortcode_attributes( shortcode );

		if (Object.keys( current_attributes ).length !== 0) {
			shortcode = get_new_customize_shortcode( current_attributes, cus_attr_name, cus_attr_value );
		}
	}

	return shortcode;
}

function get_new_customize_shortcode( current_attributes, cus_attr_name, cus_attr_value ) {
	var attribute_content = '';
	var old_customize_attributes = [];

	current_attributes[cus_attr_name] = cus_attr_value; // Add/Update new customize value

	// Build default attributes and newest customize before
	for (const property in current_attributes) {
		if ((default_lasso_shortcode_attributes.indexOf(property) !== -1) || (property === cus_attr_name) ) {
			var value = current_attributes[property];
			if ( toogle_attributes.includes(property) ) { // Toogle attributes
				let attr_value = current_attributes[property] ? 'show' : 'hide';

				// Add "hide" value for toogle attribute, else do nothing
				if ( 'hide' === attr_value ) {
					attribute_content += ' ' + property + '="' + attr_value + '"';
				}
			} else if (value) { // Text box attributes
				attribute_content += ' ' + property + '="' + current_attributes[property] + '"';
			}
		} else {
			old_customize_attributes.push(property);
		}
	}

	// Build old customize attributes later
	old_customize_attributes.forEach(old_cuz_attr => {
		var value = current_attributes[old_cuz_attr];
		if (value) {
			attribute_content += ' ' + old_cuz_attr + '="' + current_attributes[old_cuz_attr] + '"';
		}
	});

	return '[lasso' + attribute_content + ']';
}

function init_editor_fields ( view, model ) {
	let shortcode          = parent.jQuery(el_lasso_shortcode_textarea).val();
	let current_attributes = get_lasso_shortcode_attributes(shortcode);
	let display_type       = current_attributes['type'] ? current_attributes['type'] : 'single';

	jQuery.each( parent.jQuery('#elementor-controls .elementor-control'), function( key, elementor_control ) {
		let element_control_class = parent.jQuery(elementor_control).attr('class');

		if ( element_control_class.search('content_section') >= 0 /*|| element_control_class.search('lasso_shortcode') >= 0*/ ) {
			return;
		}

		if ( element_control_class.search(display_type) < 0 ){
			parent.jQuery(elementor_control).hide();
		} else {
			let input     = parent.jQuery(elementor_control).find('input');
			let attr_name = input.data('setting').replace('_' + display_type, '');

			if ( input.hasClass('elementor-switch-input') ) {
				jQuery(input).change(function(){
					if ( parent.jQuery(el_lasso_shortcode_textarea).length > 0 ) {
						let shortcode_customize = customize_shortcode(attr_name, jQuery(this).prop( 'checked' ))

						parent.jQuery(el_lasso_shortcode_textarea).val(shortcode_customize);
						model.attributes.settings.attributes.lasso_shortcode = shortcode_customize;
						view.$el.find('.shortcode-input').val(shortcode_customize);
					}

				});
			} else {
				jQuery(input).keyup(function(){
					if ( parent.jQuery(el_lasso_shortcode_textarea).length > 0 ) {
						let shortcode_customize = customize_shortcode(attr_name, jQuery(this).val())

						parent.jQuery(el_lasso_shortcode_textarea).val(shortcode_customize);
						model.attributes.settings.attributes.lasso_shortcode = shortcode_customize;
						view.$el.find('.shortcode-input').val(shortcode_customize);
					}
				});
			}
		}
	});
}

function init_lasso_display_add_modal(options = {}) {
	console.log('Init lasso display add modal');
	lasso_display_add_modal = new LassoModal('lasso-display-add', options);

	// reset pop-up on close
	lasso_display_add_modal.onModalHidden( function () {
		jQuery("#lasso-display-type").removeClass("d-none");
		jQuery("#lasso-display-add .tab-container").addClass("d-none");
		jQuery("#lasso-display-add .tab-container .lasso-items").html('');

		jQuery("#lasso-display-add .btn-generate-text").text("Generate");
		jQuery("#lasso-display-add #prompt").val('');
		jQuery("#lasso-display-add .lasso-response").addClass("d-none");
	});

	return lasso_display_add_modal;
}
