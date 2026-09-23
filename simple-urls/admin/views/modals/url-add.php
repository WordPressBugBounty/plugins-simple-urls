<?php
/**
 * Modal
 *
 * @package Modal
 */

?>

<?php if ( ! isset( $is_from_editor ) ) : ?>
<!-- URL ADD -->
<div class="modal fade" id="url-add" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content shadow p-5 rounded text-center">
			<?php require SIMPLE_URLS_DIR . '/admin/views/modals/partials/url-add-modal-inner.php'; ?>
		</div>
	</div>
</div>
<?php else : ?>
	<!-- Post Editor -->
	<!-- URL ADD -->
	<div class="lasso-modal url-add" id="url-add" tabindex="-1" role="dialog" data-is-from-editor="1">
		<div class="modal-content shadow p-5 rounded text-center lasso-modal-content modal-sm">
			<?php require SIMPLE_URLS_DIR . '/admin/views/modals/partials/url-add-modal-inner.php'; ?>
		</div>
	</div>
<?php endif; ?>
