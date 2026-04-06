<?php
/**
 * Unsaved changes confirmation modal
 *
 * @package LassoLite
 */

// phpcs:ignore
?>

<!-- UNSAVED CHANGES CONFIRMATION -->
<div class="modal fade" id="unsaved-changes" tabindex="-1" role="dialog" aria-labelledby="unsaved-changes-title" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog" role="document">
		<div class="modal-content shadow rounded">

			<div class="modal-header border-0 pb-0">
				<h2 class="modal-title font-weight-bold mb-0" id="unsaved-changes-title"><?php echo esc_html__( 'Unsaved Changes', 'simple-urls' ); ?></h2>
				<button type="button" class="close" data-dismiss="modal" aria-label="<?php echo esc_attr__( 'Close', 'simple-urls' ); ?>">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>

			<div class="modal-body">
				<p class="mb-0"><?php echo esc_html__( 'You have unsaved changes that will be lost if you leave this page. What would you like to do?', 'simple-urls' ); ?></p>
			</div>

			<div class="modal-footer border-0 justify-content-end">
				<button type="button" class="btn btn-outline-secondary badge-pill font-weight-bold hover-down mx-1" data-dismiss="modal">
					<?php echo esc_html__( 'Leave Without Saving', 'simple-urls' ); ?>
				</button>
				<button type="button" class="btn green-bg white badge-pill font-weight-bold hover-green hover-down mx-1 shadow">
					<?php echo esc_html__( 'Save Changes', 'simple-urls' ); ?>
				</button>
			</div>

		</div>
	</div>
</div>
