jQuery(document).ready(function() {
	jQuery(document)
		.on('click', '#onboarding_container .tab-item .next-step', go_to_next_step)
		.on('click', '#onboarding_container .progressbar_container .progressbar li', access_step)
		.on('click', process_up_sell_modal);
});

function go_to_next_step() {
	go_to_next_step_action(this);
}

function go_to_next_step_action( tab_item_child_element ) {
	let current_tab_container = jQuery(tab_item_child_element).closest('.tab-item');
	let next_tab_container    = current_tab_container.next('.tab-item');

	if ( next_tab_container.length ) {
		current_tab_container.addClass('d-none');
		next_tab_container.removeClass('d-none');

		window.scrollTo(0, 0);
	}
}

function access_step() {
	let access_step = jQuery(this).data('step');

	if ( access_step ) {
		let access_step_tab = jQuery('.tab-item[data-step="' + access_step + '"]');
		if ( access_step_tab.length ) {
			jQuery('#onboarding_container .tab-item').addClass('d-none');
			access_step_tab.removeClass('d-none');
		}

	}
}

function process_up_sell_modal( event ) {
	let up_sell_modal = jQuery('#up-sell-modal');
	let lite_container = jQuery('.lite-container');

	if ( up_sell_modal.length && lite_container.length ) {
		let up_sell_modal_w = up_sell_modal.width();
		let maximum_left = lite_container.width() + lite_container.offset().left;
		let lasso_lite_disabled_wrapper = jQuery(event.target).closest('.lasso-lite-disabled');
		let support = lasso_lite_helper.get_url_parameter('support');

		if ( lasso_lite_disabled_wrapper.length !== 0 && support === null ) {
			let x = ( event.pageX - 150 );
			if ( x + up_sell_modal_w + 160 > maximum_left ) {
				x = maximum_left - up_sell_modal_w - 200;
			}
			up_sell_modal.css('left', x + "px");
			up_sell_modal.css('top', ( event.pageY - 10 ) + "px");
			up_sell_modal.css('display', 'block');
		} else {
			up_sell_modal.css('display', 'none');
		}
	}
}
