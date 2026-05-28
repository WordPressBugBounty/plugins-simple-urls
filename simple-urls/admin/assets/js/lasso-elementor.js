let window_url_detail;
let view_elementor;
let model_elementor;
let el_lasso_shortcode_textarea        = '.elementor-control-lasso_shortcode.elementor-control-type-textarea .elementor-control-tag-area';
let shortcodes_reload                  = [];
let customizing_display                = (typeof lassoLiteOptionsData !== 'undefined' && ( lassoLiteOptionsData.customizing_display || lassoLiteOptionsData.block_customize ) ) || [];
let toogle_attributes                  = customizing_display['toogle_attributes'] || [];
let default_lasso_shortcode_attributes = ['ref', 'id', 'link_id', 'type', 'category'];
let modal_display_popup_html           = '';
/** Singleflight + UI state for "Add a Display" (parity with affiliate-plugin Elementor). */
let modal_display_popup_request        = null;
let modal_display_popup_status         = 'idle';
/** Where to inject modal markup when the widget template does not host it (see ensureLiteElementorDisplayModal). */
let lasso_lite_elementor_modal_context = null;
let allow_pages                        = [];
let current_page                       = 'elementor';
let loading_html                       = '<div class="py-5"><div class="loader"></div></div>';
/** Set when opening Add/Select display from a Lasso Elementor widget (preview), so we can refresh shortcode-html after pick. */
let lasso_elementor_active_widget_id   = null;

if ( typeof window.lasso_segment_tracking !== 'function' ) {
	window.lasso_segment_tracking = function() {};
}

/**
 * Panel textarea is often empty until the widget is selected; preview HTML already has .shortcode-input from saved settings.
 *
 * @param {jQuery} $scope Widget root (.elementor-element-*).
 * @return {string}
 */
function resolveLassoShortcodeForPreview( $scope ) {
	let sc = '';
	if ( $scope && $scope.length ) {
		sc = ( $scope.find( '.shortcode-input' ).first().val() || '' ).trim();
	}
	if ( ! sc && typeof parent !== 'undefined' && parent.jQuery ) {
		sc = ( parent.jQuery( el_lasso_shortcode_textarea ).val() || '' ).trim();
	}
	return sc;
}

/**
 * Lasso widget markup renders in the preview iframe; panel scripts run in the parent editor window.
 *
 * @return {jQuery}
 */
function lassoLiteElementorPreviewDocument() {
	if ( window.elementor && elementor.$preview && elementor.$preview.length ) {
		try {
			var framed = elementor.$preview[0].contentDocument;
			if ( framed ) {
				return jQuery( framed );
			}
		} catch ( err ) {}
	}
	return jQuery( document );
}

/**
 * Match content_template() button visibility to the real shortcode (panel + preview often diverge until renderOnChange).
 *
 * @param {string|number} widgetId Elementor element id.
 * @param {string} shortcode Current lasso shortcode / textarea value.
 */
function syncLassoElementorToolbarVisibility( widgetId, shortcode ) {
	var $widget = lassoLiteElementorPreviewDocument().find( '.elementor-element-' + widgetId );
	if ( ! $widget.length ) {
		return;
	}
	var hasShortcode = shortcode && String( shortcode ).trim().length > 0;
	$widget.find( '.lasso-lite-elementor-chrome' ).css( 'display', 'block' );
	$widget.find( '.lasso-update-display' ).css( 'display', hasShortcode ? 'inline-block' : 'none' );
	$widget.find( '.lasso-edit-display' ).css( 'display', hasShortcode ? 'inline-block' : 'none' );
	$widget.find( '.btn-modal-add-display' ).css( 'display', 'inline-block' );
	var $input = $widget.find( '.shortcode-input' ).first();
	var $prevSpan = $input.prev( 'span' );
	if ( $prevSpan.length ) {
		$prevSpan.css( 'display', hasShortcode ? 'none' : 'block' );
	}
	$input.css( 'display', hasShortcode ? 'block' : 'none' );
}

/**
 * Disable / relabel every "Add a Display" control while modal HTML is loading (Pro Elementor parity).
 *
 * @param {string} status 'loading' | 'error' | 'ready' | 'idle' (idle = normal label).
 */
function setElementorDisplayButtonState( status ) {
	modal_display_popup_status = status;
	lassoLiteElementorPreviewDocument().find( '.btn-modal-add-display' ).each( function() {
		var button = jQuery( this );
		var originalLabel = button.data( 'original-label' );
		if ( ! originalLabel ) {
			originalLabel = button.text().trim() || 'Add a Display';
			button.data( 'original-label', originalLabel );
		}
		if ( 'loading' === status ) {
			button.prop( 'disabled', true ).text( 'Loading...' );
		} else if ( 'error' === status ) {
			button.prop( 'disabled', false ).text( 'Retry Add a Display' );
		} else {
			button.prop( 'disabled', false ).text( originalLabel );
		}
	} );
}

function ensureLiteElementorDisplayModal() {
	var popup = jQuery( '#lasso-display-add' );
	if ( popup.length ) {
		return popup;
	}
	var ctx = lasso_lite_elementor_modal_context;
	var $widget = ctx && ctx.$widget && ctx.$widget.length ? ctx.$widget : null;
	var appendToWidget = $widget && ! ctx.hasHostedModal;
	var $host;
	if ( appendToWidget ) {
		$host = $widget;
	} else if ( jQuery( '#wpcontent' ).length ) {
		$host = jQuery( '#wpcontent' );
	} else {
		$host = jQuery( 'body' );
	}
	$host.append( modal_display_popup_html );
	init_lasso_display_add_modal( appendToWidget ? { destroyEventsAfterClose: true } : {} );
	return jQuery( '#lasso-display-add' );
}

function bindElementorMainNavEvents() {
	var popup = jQuery( '#lasso-display-add' );
	var main_nav = jQuery( 'div.elementor-widget-creativo_navigation_menu' ).closest( 'section.elementor-section' );
	if ( ! popup.length || ! main_nav.length ) {
		return;
	}
	popup.off( '.lassoElementorMainNav' );
	popup.on( 'ls.modal.shown.lassoElementorMainNav', function() {
		main_nav.hide();
	} );
	popup.on( 'ls.modal.hidden.lassoElementorMainNav', function() {
		main_nav.show();
	} );
}

function openLiteElementorDisplayModal() {
	if ( 'loading' === modal_display_popup_status ) {
		return;
	}
	if ( ! modal_display_popup_html ) {
		loadPopupContent().done( function() {
			openLiteElementorDisplayModal();
		} );
		return;
	}
	ensureLiteElementorDisplayModal();
	bindElementorMainNavEvents();
	lasso_segment_tracking( 'Open "Choose a Display Type" Popup' );
	window.lasso_display_add_modal.open();
}

function loadPopupContent() {
	if ( modal_display_popup_html ) {
		setElementorDisplayButtonState( 'ready' );
		return jQuery.Deferred().resolve( modal_display_popup_html ).promise();
	}
	if ( modal_display_popup_request ) {
		return modal_display_popup_request;
	}
	setElementorDisplayButtonState( 'loading' );
	modal_display_popup_request = jQuery.ajax( {
		url: lassoLiteOptionsData.ajax_url,
		type: 'get',
		data: {
			action: 'lasso_lite_get_display_html',
			nonce: lassoLiteOptionsData.optionsNonce,
		},
	} )
		.done( function( res ) {
			if ( res && res.data && res.data.html ) {
				modal_display_popup_html = res.data.html;
			}
			setElementorDisplayButtonState( 'ready' );
		} )
		.fail( function() {
			setElementorDisplayButtonState( 'error' );
		} )
		.always( function() {
			modal_display_popup_request = null;
		} );
	return modal_display_popup_request;
}

/**
 * Same source order as the "Update Display" click handler: panel textarea, then preview .shortcode-input.
 *
 * @param {string|number|null|undefined} id Elementor element id.
 * @return {string}
 */
function lassoLiteElementorResolveShortcodeForUpdateDisplay( id ) {
	if ( id == null || id === '' ) {
		return '';
	}
	var idStr = String( id );
	var shortcode = '';
	if ( typeof parent !== 'undefined' && parent.jQuery && parent.jQuery( el_lasso_shortcode_textarea ).length > 0 ) {
		shortcode = ( parent.jQuery( el_lasso_shortcode_textarea ).val() || '' ).trim();
	}
	if ( ! shortcode ) {
		var $inp = lassoLiteElementorPreviewDocument().find( '.elementor-element-' + idStr ).find( '.elementor-widget-lasso_shortcode .shortcode-input' ).first();
		if ( $inp.length ) {
			shortcode = ( $inp.val() || '' ).trim();
		}
	}
	return shortcode;
}

/**
 * After the shortcode is updated (panel, preview, or customize fields), same effect as clicking "Update Display".
 *
 * @param {string|number} widgetId Elementor element id.
 */
function lassoLiteElementorProgrammaticUpdateDisplayClick( widgetId ) {
	var idStr = String( widgetId );
	if ( ! idStr ) {
		return;
	}
	var latest = lassoLiteElementorResolveShortcodeForUpdateDisplay( widgetId );
	syncLassoElementorToolbarVisibility( widgetId, latest );
	if ( ! latest ) {
		return;
	}
	var $btn = lassoLiteElementorPreviewDocument().find( '.elementor-element-' + idStr ).find( '.lasso-update-display' ).first();
	if ( $btn.length ) {
		$btn.trigger( 'click' );
	} else {
		getLassoShortcodeHtml( widgetId, latest, false, true );
	}
}

function getShortcodeFromElementorModel( model ) {
	if ( ! model ) {
		return '';
	}
	try {
		var shortcode = '';
		var settings = model.get( 'settings' );
		if ( settings && typeof settings.get === 'function' ) {
			shortcode = ( settings.get( 'lasso_shortcode' ) || '' ).trim();
		}
		if ( ! shortcode && model.attributes && model.attributes.settings && model.attributes.settings.attributes ) {
			shortcode = ( model.attributes.settings.attributes.lasso_shortcode || '' ).trim();
		}
		return shortcode;
	} catch ( err ) {
		return '';
	}
}

/** Debounce timers per Elementor element id for shortcode → preview AJAX. */
let lasso_lite_preview_refresh_timers = {};
/** Skip duplicate identical preview requests (in flight or just succeeded). User actions pass forceRefresh. */
let lasso_lite_preview_inflight = {};
let lasso_lite_preview_last_ok = {};

/**
 * After the shortcode changes in the editor, refresh the canvas preview (lasso_lite_get_shortcode_content), debounced.
 *
 * @param {string|number} widgetId Elementor element id.
 * @param {string} shortcode New shortcode string.
 */
function scheduleLassoLiteElementorShortcodePreview( widgetId, shortcode ) {
	var idStr = String( widgetId );
	var sc = ( shortcode || '' ).trim();
	syncLassoElementorToolbarVisibility( widgetId, sc );
	if ( ! sc ) {
		return;
	}
	if ( lasso_lite_preview_refresh_timers[ idStr ] ) {
		clearTimeout( lasso_lite_preview_refresh_timers[ idStr ] );
	}
	lasso_lite_preview_refresh_timers[ idStr ] = setTimeout( function() {
		delete lasso_lite_preview_refresh_timers[ idStr ];
		lassoLiteElementorProgrammaticUpdateDisplayClick( widgetId );
	}, 450 );
}

/** True after first startup sweep schedule (frontend/init and/or late script). */
let lasso_lite_startup_sweeps_scheduled = false;

/**
 * After full editor canvas is up, sync every Lasso Lite widget (toolbar + AJAX preview).
 * element_ready can run before Elementor finishes hydrating .shortcode-input / template.
 */
function sweepLassoLiteElementorWidgetsForPreview() {
	jQuery( '.elementor-widget-lasso_shortcode' ).each( function() {
		var $wl = jQuery( this );
		var $scope = $wl.closest( '.elementor-element' );
		if ( ! $scope.length ) {
			return;
		}
		var id = lassoLiteElementorElementId( $scope );
		if ( ! id ) {
			return;
		}
		var sc = resolveLassoShortcodeForPreview( $scope );
		if ( ! sc ) {
			return;
		}
		syncLassoElementorToolbarVisibility( id, sc );
		$scope.find( 'div.shortcode-html' ).first().html( loading_html );
		getLassoShortcodeHtml( id, sc, false );
	} );
}

/**
 * Elementor widget root: has data-id (do not rely on [data-element_type] for .data('id')).
 *
 * @param {jQuery} $from Any node inside the widget.
 * @return {jQuery}
 */
function lassoLiteElementorWidgetRoot( $from ) {
	return $from.closest( '.elementor-element' );
}

/**
 * Elementor stores id on data-id; prefer attr() so we are not bitten by jQuery .data() parse/cache.
 *
 * @param {jQuery} $root .elementor-element
 * @return {string|number|null}
 */
function lassoLiteElementorElementId( $root ) {
	if ( ! $root || ! $root.length ) {
		return null;
	}
	var id = $root.attr( 'data-id' );
	if ( typeof id === 'string' && id.length ) {
		return id;
	}
	var cached = $root.data( 'id' );
	return cached != null && String( cached ).length ? cached : null;
}

/**
 * Preview iframe: elementorFrontend may not exist yet when this file runs (no elementor-frontend script dependency).
 * Bind once it is available; retries + elementor/frontend/init cover late init and missed events.
 */
function lassoLiteBindElementorPreviewCanvas() {
	if ( window.lassoLiteElementorPreviewCanvasBound ) {
		return;
	}
	if ( ! window.elementorFrontend || ! elementorFrontend.hooks ) {
		return;
	}
	var inEditMode = typeof elementorFrontend.isEditMode === 'function' && elementorFrontend.isEditMode();
	var loc = typeof window.location !== 'undefined' ? window.location : null;
	var previewLikeUrl = loc && loc.href && loc.href.indexOf( 'elementor-preview' ) !== -1;
	if ( ! inEditMode && ! previewLikeUrl ) {
		return;
	}
	window.lassoLiteElementorPreviewCanvasBound = true;

	function lassoLiteOnLassoShortcodeElementReady( $scope ) {
		var $root = lassoLiteElementorWidgetRoot( $scope );
		let id = lassoLiteElementorElementId( $root );
		setElementorDisplayButtonState( modal_display_popup_status );

		setTimeout( function() {
			syncLassoElementorToolbarVisibility( id, resolveLassoShortcodeForPreview( $root ) );
		}, 0 );

		let attempts = 0;

		function tryLoadPreview() {
			let shortcode = resolveLassoShortcodeForPreview( $root );
			if ( shortcode ) {
				syncLassoElementorToolbarVisibility( id, shortcode );
				$root.find( 'div.shortcode-html' ).first().html( loading_html );
				getLassoShortcodeHtml( id, shortcode, false );
				return;
			}
			if ( attempts < 50 ) {
				attempts++;
				setTimeout( tryLoadPreview, 100 );
			}
		}

		tryLoadPreview();

		// ? One late refresh if the box is still empty (hydration edge); avoid fixed triple timers → 4× AJAX.
		setTimeout( function() {
			var sc = resolveLassoShortcodeForPreview( $root );
			if ( ! sc || ! id ) {
				return;
			}
			var $box = $root.find( 'div.shortcode-html' ).first();
			var stillLoading = $box.length && $box.find( '.loader' ).length > 0;
			var noHtml = ! $box.length || ! String( $box.html() || '' ).trim().length;
			if ( noHtml || stillLoading ) {
				syncLassoElementorToolbarVisibility( id, sc );
				$box.html( loading_html );
				getLassoShortcodeHtml( id, sc, false );
			}
		}, 2200 );
	}

	elementorFrontend.hooks.addAction( 'frontend/element_ready/lasso_shortcode.default', lassoLiteOnLassoShortcodeElementReady );

	function scheduleLassoLiteStartupSweeps() {
		if ( lasso_lite_startup_sweeps_scheduled ) {
			return;
		}
		lasso_lite_startup_sweeps_scheduled = true;
		[ 0, 400, 2000 ].forEach( function( delay ) {
			setTimeout( sweepLassoLiteElementorWidgetsForPreview, delay );
		} );
	}

	elementorFrontend.hooks.addAction( 'frontend/init', scheduleLassoLiteStartupSweeps );
	setTimeout( scheduleLassoLiteStartupSweeps, 0 );
}

jQuery( function( $ ) {
	loadPopupContent();

	// ? Parent editor: panel + model (elementor.hooks). Preview iframe often has no hooks — do not gate on elementorFrontend.
	if ( window.elementor && elementor.hooks && ! window.lassoLiteElementorPanelHookRegistered ) {
		window.lassoLiteElementorPanelHookRegistered = true;
		elementor.hooks.addAction( 'panel/open_editor/widget/lasso_shortcode', function( panel, model, view ) {
			let content_tab_el = '.elementor-panel-navigation .elementor-tab-control-content';
			view_elementor     = view;
			model_elementor    = model;
			setElementorDisplayButtonState( modal_display_popup_status );

			let elId = model.get( 'id' );
			if ( elId && typeof jQuery !== 'undefined' ) {
				let $doc = jQuery( document );
				$doc.off( 'input.lassoLiteScPreview change.lassoLiteScPreview', el_lasso_shortcode_textarea );
				$doc.on( 'input.lassoLiteScPreview change.lassoLiteScPreview', el_lasso_shortcode_textarea, function() {
					scheduleLassoLiteElementorShortcodePreview( elId, jQuery( this ).val() );
				} );
			}

			init_editor_fields( view, model );

			jQuery( document ).on( 'click', content_tab_el, function( e ) {
				init_editor_fields( view, model );
				setElementorDisplayButtonState( modal_display_popup_status );
			} );

			view.renderOnChange();
			setElementorDisplayButtonState( modal_display_popup_status );

			let shortcodeFromModel = getShortcodeFromElementorModel( model );
			if ( elId ) {
				syncLassoElementorToolbarVisibility( elId, shortcodeFromModel );
				setTimeout( function() {
					syncLassoElementorToolbarVisibility( elId, getShortcodeFromElementorModel( model ) );
				}, 0 );
			}

			if ( elId && shortcodeFromModel ) {
				lassoLiteElementorPreviewDocument().find( '.elementor-element-' + elId ).find( 'div.shortcode-html' ).html( loading_html );
				getLassoShortcodeHtml( elId, shortcodeFromModel, false, true );
			}
		} );
	}

	// ? Preview canvas: wait for elementorFrontend (avoid racing Elementor's bundle when not a script dependency).
	lassoLiteBindElementorPreviewCanvas();
	jQuery( window ).on( 'elementor/frontend/init', lassoLiteBindElementorPreviewCanvas );
	[ 0, 50, 150, 400, 1000, 2500, 5000, 12000 ].forEach( function( ms ) {
		setTimeout( lassoLiteBindElementorPreviewCanvas, ms );
	} );
});

jQuery(document).ready(function () {
	jQuery(document).on('click', '.elementor-element-edit-mode.elementor-widget-lasso_shortcode .elementor-editor-widget-settings', function(){
		view_elementor.renderOnChange();
		setElementorDisplayButtonState( modal_display_popup_status );
	});

	jQuery(document).on('click', '.btn-modal-add-display', function(){
		var $el = lassoLiteElementorWidgetRoot( jQuery( this ) );
		lasso_elementor_active_widget_id = $el.length ? lassoLiteElementorElementId( $el ) : null;
		var $widget = jQuery( this ).closest( '.elementor-widget-lasso_shortcode' );
		lasso_lite_elementor_modal_context = {
			$widget: $widget,
			hasHostedModal: $widget.hasClass( 'lasso-modal' ),
		};
		setTimeout( function() {
			openLiteElementorDisplayModal();
		}, 50 );
	});

	jQuery(document).on('click', '.lasso-update-display', function (){
		var $root = lassoLiteElementorWidgetRoot( jQuery( this ) );
		let id = lassoLiteElementorElementId( $root );
		let shortcode = lassoLiteElementorResolveShortcodeForUpdateDisplay( id );

		if ( shortcode && id ) {
			getLassoShortcodeHtml( id, shortcode, false, true );
		}
	});

	jQuery(document).on('click', '.lasso-edit-display', function (){
		var $root = lassoLiteElementorWidgetRoot( jQuery( this ) );
		let id        = lassoLiteElementorElementId( $root );
		let $widget   = jQuery(this).closest('.elementor-widget-lasso_shortcode');
		let shortcode = '';
		if ( parent.jQuery(el_lasso_shortcode_textarea).length > 0 ) {
			shortcode = ( parent.jQuery(el_lasso_shortcode_textarea).val() || '' ).trim();
		}
		if ( ! shortcode && $widget.length ) {
			shortcode = ( $widget.find('.shortcode-input').first().val() || '' ).trim();
		}

		if ( shortcode && id ) {
			open_url_detail_window( id, shortcode )
		}
	});

	jQuery(document).on('keyup', '.shortcode-input', function (e){
		if ( parent.jQuery(el_lasso_shortcode_textarea).length > 0 ) {
			// ? Update input lasso shortcode on change
			parent.jQuery(el_lasso_shortcode_textarea).val(jQuery(this).val());
			if ( model_elementor && model_elementor.attributes && model_elementor.attributes.settings && model_elementor.attributes.settings.attributes ) {
				model_elementor.attributes.settings.attributes.lasso_shortcode = jQuery(this).val();
			}
		}
		var previewId = lassoLiteElementorElementId( lassoLiteElementorWidgetRoot( jQuery( this ) ) );
		scheduleLassoLiteElementorShortcodePreview( previewId, jQuery(this).val() );
	});

	jQuery(document).on('focusout', '.shortcode-input', function (e){
		var previewId = lassoLiteElementorElementId( lassoLiteElementorWidgetRoot( jQuery( this ) ) );
		var idStr = previewId ? String( previewId ) : '';
		if ( idStr && lasso_lite_preview_refresh_timers[ idStr ] ) {
			clearTimeout( lasso_lite_preview_refresh_timers[ idStr ] );
			delete lasso_lite_preview_refresh_timers[ idStr ];
		}
		if ( parent.jQuery(el_lasso_shortcode_textarea).length > 0 && model_elementor ) {
			model_elementor.renderRemoteServer();
		}
		var sc = ( jQuery(this).val() || '' ).trim();
		if ( previewId && sc ) {
			syncLassoElementorToolbarVisibility( previewId, sc );
			getLassoShortcodeHtml( previewId, sc, false, true );
		}
	});

	jQuery(document).on('click', '.ql-editor', function(){
		if ( window.quill ) {
			window.quill.focus();
		}
	});

});

function getLassoShortcodeHtml( id, shortcode, is_new_display, forceRefresh ) {
	if ( typeof lassoLiteOptionsData === 'undefined' ) {
		return;
	}
	var idStr = String( id );
	var sc = ( shortcode || '' ).trim();
	if ( ! idStr || ! sc ) {
		return;
	}
	var inflight = lasso_lite_preview_inflight[ idStr ];
	if ( inflight && inflight.sc === sc && ! is_new_display && ! forceRefresh ) {
		return;
	}
	if ( inflight && inflight.jqXHR && inflight.sc !== sc ) {
		try {
			inflight.jqXHR.abort();
		} catch ( err ) {}
		delete lasso_lite_preview_inflight[ idStr ];
	}
	if ( ! is_new_display && ! forceRefresh ) {
		var lastOk = lasso_lite_preview_last_ok[ idStr ];
		var now = Date.now();
		// ? Wider window: startup sweeps + Elementor retries otherwise fire 3–6 identical AJAXs per widget.
		if ( lastOk && lastOk.sc === sc && ( now - lastOk.t ) < 4000 ) {
			return;
		}
	}
	function $getPreviewBox() {
		var $ctx = lassoLiteElementorPreviewDocument();
		var $box = $ctx.find( '.elementor-element-' + id ).find( '.elementor-widget-lasso_shortcode div.shortcode-html' ).first();
		if ( ! $box.length ) {
			$box = $ctx.find( '.elementor-element-' + id ).find( 'div.shortcode-html' ).first();
		}
		return $box;
	}
	function renderPreviewLoadFailed() {
		$getPreviewBox().html(
			'<div class="py-3 px-2 text-left" style="background:#fff;color:#b00;font-size:13px;">' +
			'Could not load the display preview. Try <strong>Update Display</strong> or reload the editor.' +
			'</div>'
		);
	}
	lasso_lite_preview_inflight[ idStr ] = { sc: sc, jqXHR: null };
	var jqXHR = jQuery.ajax({
		url: lassoLiteOptionsData.ajax_url,
		type: 'post',
		data: {
			action: 'lasso_lite_get_shortcode_content',
			nonce: lassoLiteOptionsData.optionsNonce,
			shortcode: shortcode,
		},
		beforeSend: function( xhr ) {
			syncLassoElementorToolbarVisibility( id, shortcode );
			$getPreviewBox().html( loading_html );
		}
	})
		.done( function( res ) {
			if ( ! res || ! res.success || ! res.data ) {
				renderPreviewLoadFailed();
				return;
			}
			var htmlOut = res.data.html || '';
			$getPreviewBox().html( htmlOut );
			syncLassoElementorToolbarVisibility( id, shortcode );
			lasso_lite_preview_last_ok[ idStr ] = { sc: sc, t: Date.now() };

			// Tracking if Display Added
			if ( is_new_display ) {
				lasso_segment_tracking( 'Display Added', {
					shortcode: shortcode
				} );
			}
		} )
		.fail( function() {
			renderPreviewLoadFailed();
		} )
		.always( function() {
			var cur = lasso_lite_preview_inflight[ idStr ];
			if ( cur && cur.jqXHR === jqXHR ) {
				delete lasso_lite_preview_inflight[ idStr ];
			}
			lassoLiteElementorPreviewDocument().find( '.elementor-element-' + id ).find( 'div.py-5' ).remove();
		} );
	lasso_lite_preview_inflight[ idStr ].jqXHR = jqXHR;
}

function add_short_code_elementor(shortcode) {
	var shortcode_control = parent.jQuery( el_lasso_shortcode_textarea );
	if ( 0 === shortcode_control.length ) {
		shortcode_control = jQuery( el_lasso_shortcode_textarea );
	}
	if ( shortcode_control.length ) {
		shortcode_control.val( shortcode ).trigger( 'input' ).trigger( 'change' );
	}
	if (
		model_elementor
		&& model_elementor.attributes
		&& model_elementor.attributes.settings
		&& model_elementor.attributes.settings.attributes
	) {
		model_elementor.attributes.settings.attributes.lasso_shortcode = shortcode;
	}
	var previewId = lasso_elementor_active_widget_id;
	if ( previewId && shortcode ) {
		lassoLiteElementorPreviewDocument().find( '.elementor-element-' + previewId ).find( '.shortcode-input' ).first().val( shortcode );
	}

	clean_modal();
	trigger_load_preview();

	if ( previewId && shortcode ) {
		syncLassoElementorToolbarVisibility( previewId, shortcode );
		getLassoShortcodeHtml( previewId, shortcode, true );
	}
	lasso_elementor_active_widget_id = null;
}

function clean_modal(){
	window.lasso_display_add_modal.close();
}

function trigger_load_preview() {
	if ( model_elementor && 'function' === typeof model_elementor.renderRemoteServer ) {
		model_elementor.renderRemoteServer();
	}
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
					getLassoShortcodeHtml( id, shortcodes_reload[ i ].shortcode, false, true );
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
						scheduleLassoLiteElementorShortcodePreview( model.get( 'id' ), shortcode_customize );
					}

				});
			} else {
				jQuery(input).keyup(function(){
					if ( parent.jQuery(el_lasso_shortcode_textarea).length > 0 ) {
						let shortcode_customize = customize_shortcode(attr_name, jQuery(this).val())

						parent.jQuery(el_lasso_shortcode_textarea).val(shortcode_customize);
						model.attributes.settings.attributes.lasso_shortcode = shortcode_customize;
						view.$el.find('.shortcode-input').val(shortcode_customize);
						scheduleLassoLiteElementorShortcodePreview( model.get( 'id' ), shortcode_customize );
					}
				});
			}
		}
	});
}
