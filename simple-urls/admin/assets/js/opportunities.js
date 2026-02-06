jQuery(function($) {
	var $modal = $('#lasso-lite-pro-modal');
	if (!$modal.length) {
		return;
	}

	$modal.on('show.bs.modal', function() {
		$('body').addClass('lasso-lite-pro-modal-open');
	});
	$modal.on('hidden.bs.modal', function() {
		$('body').removeClass('lasso-lite-pro-modal-open');
	});

	$modal.modal('show');
});
