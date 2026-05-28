jQuery(document).ready(function () {
	jQuery(function() {
		let limit       = 5;
		let currentPage = 1;
		let tab         = 'single';
		/** Same as lasso-lite-gutenberg-block.js (inline SVG — no .loader / .py-5 in block editor). */
		var LASSO_LITE_INLINE_LOADING_HTML =
			'<div class="lasso-lite-inline-loading" style="display:flex!important;align-items:center!important;justify-content:center!important;min-height:min(40vh,220px)!important;width:100%!important;box-sizing:border-box!important;padding:12px;">' +
			'<svg width="40" height="40" viewBox="0 0 50 50" style="flex-shrink:0" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">' +
			'<circle cx="25" cy="25" r="20" fill="none" stroke="#E2E2E2" stroke-width="5"/>' +
			'<circle cx="25" cy="25" r="20" fill="none" stroke="#22baa0" stroke-width="5" stroke-dasharray="31.4 94.2" stroke-linecap="round">' +
			'<animateTransform attributeName="transform" type="rotate" from="0 25 25" to="360 25 25" dur="0.85s" repeatCount="indefinite"/>' +
			'</circle></svg></div>';
		/** Block editor + other screens: never use global #id (ambiguous). Always scope to the Lasso modal root. */
		var $lassoDisplayModal = function() {
			return jQuery( '#lasso-display-add' );
		};
		var $lassoDisplayAllLinks = function() {
			return $lassoDisplayModal().find( '#all_links' );
		};

		jQuery(document)
			.on('click', '.lasso-display-type', add_display)
			.on('click', '.lasso-display-add-btn.add-btn', add_short_code_single_main)
			.on('click', '.btn-create-link', show_create_link_modal)
			.on('hidden.bs.modal', '#lasso-display-add', reset_pop_up_display_modal)
			.on('keyup', '.search-keys input', function(e) {
				// WHEN ENTER IS PRESSED, SEARCH
				if (e.which === 13) {
					single_list(true);
				}
			});

		/**
		 * Show modal Choose a Display Type
		 */
		function add_display() {
			tab = jQuery(this).data('tab');
			show_tab( jQuery(this).data('tab-container') );
		}

		/**
		 * Show selected tab.
		 *
		 * @param tab_container
		 */
		function show_tab(tab_container) {
			var $m = $lassoDisplayModal();
			let tab_container_el = $m.find( '#' + tab_container );
			$m.find( '#lasso-display-type' ).addClass( 'd-none' );
			tab_container_el.removeClass( 'd-none' );
			tab_container_el.find( '.search-keys input' ).addClass( 'd-none' );
			tab_container_el.find( '.search-keys input#search-key-' + tab ).removeClass( 'd-none' );

			if(tab === 'single') {
				single_list();
			}
		}

		function single_list(entering_search = false) {
			var $m = $lassoDisplayModal();
			let keyword      = $m.find( '.search-keys input#search-key-' + tab ).val();
			let current_page = get_current_page(entering_search);

			jQuery.ajax({
				url  : lassoLiteOptionsData.ajax_url,
				type : 'post',
				data : {
					action  : 'lasso_lite_get_single',
					nonce   : lassoLiteOptionsData.optionsNonce,
					keyword : keyword,
					limit   : limit,
					page    : current_page,
				},
				beforeSend: function() {
					show_loading();
				}
			})
				.done(function(res) {
					if (typeof res.data != 'undefined') {
						let data            = res.data;
						let json_data       = data.output;
						let single_total    = parseInt(data.total);
						let page            = data.page;
						let html_pagination = '<div id="pagination-container" class="pagination"></div>';
						let el_all_link     = $lassoDisplayAllLinks();
						currentPage         = page;

						try {
							lasso_lite_helper.inject_to_template(el_all_link, 'single-list', json_data);
						} catch (error) {
							lasso_lite_helper.inject_to_template_without_jquery_templates(el_all_link, 'single-list', json_data);
						}
						jQuery(el_all_link).append(html_pagination);
						paginator(single_total);
					}
				});
		}

		// Get tab's current page
		function get_current_page(entering_search = false) {
			if ( entering_search ) {
				currentPage = 1;
			}

			return currentPage;
		}

		function show_loading() {
			if(tab === 'single') {
				var $links = $lassoDisplayAllLinks();
				if ( $links.length ) {
					$links.html( LASSO_LITE_INLINE_LOADING_HTML );
				}
			}
		}

		function paginator(count) {
			let paginator = $lassoDisplayModal().find( '#pagination-container' ).pagination({
				items: count,
				itemsOnPage: limit,
				currentPage: currentPage,
				cssStyle: 'light-theme',
				onPageClick: function(pageNumber, event) {
					if(tab === 'single') {
						currentPage = pageNumber;
						lasso_lite_helper.remove_page_number_out_of_url();
						single_list();
					}
				}
			});
		}

		function add_short_code_single_main() {
			try { add_short_code_single_block(this); } catch (error) {}
			try { add_short_code_single(this); } catch (error) {}
		}

		function show_create_link_modal(){
			jQuery("#lasso-display-add").modal("hide");
			jQuery("#url-add").modal("show");
		}

		/**
		 * Reset pop-up on close
		 */
		function reset_pop_up_display_modal() {
			var $m = $lassoDisplayModal();
			$m.find( '#lasso-display-type' ).removeClass( 'd-none' );
			$m.find( '.tab-container' ).addClass( 'd-none' );
			$m.find( '.tab-container .lasso-items' ).html( '' );
			single_list();
		}
	});
});
