jQuery(document).ready(function() {
	jQuery(document)
		.on('click', '.lasso-lite-notice-dismiss', lasso_lite_dismiss);
});

function lasso_lite_dismiss(event) {
	if (event && event.preventDefault) {
		event.preventDefault();
	}

	var $dismiss = jQuery(this);
	if ('true' === $dismiss.attr('aria-busy')) {
		return;
	}

	var optionName = $dismiss.attr('data-option-name') || $dismiss.data('option-name');
	if (!optionName) {
		return;
	}

	if (typeof lassoLiteOptionsData === 'undefined' || !lassoLiteOptionsData.optionsNonce) {
		return;
	}

	var $notice = $dismiss.closest('.lasso-lite-notice');
	var ajaxUrl = lassoLiteOptionsData.ajax_url || '/wp-admin/admin-ajax.php';

	$dismiss.attr('aria-busy', 'true');

	jQuery.ajax({
		url: ajaxUrl,
		type: 'post',
		data: {
			action: 'lasso_lite_dismiss_notice',
			nonce: lassoLiteOptionsData.optionsNonce,
			option_name: optionName
		}
	})
		.done(function (res) {
			if (res && res.success) {
				$notice.addClass('lasso-lite-d-none');
			}
		})
		.always(function () {
			$dismiss.attr('aria-busy', 'false');
		});
}
