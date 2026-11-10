<?php
use LassoLite\Classes\Helper;
?>

<div class="tab-item d-none text-center" data-step="import">
	<?php
	echo Helper::include_with_variables(
		Helper::get_path_views_folder() . 'onboarding/onboarding-progressbar.php',
		array(
			'active_step'             => 'import',
			'should_show_import_step' => true,
		)
	);
	?>

	<div class="onboarding_header text-center">
		<h1 class="font-weight-bold"><?php esc_html_e( 'Imports', 'simple-urls' ); ?></h1>
		<p class="mb-2"><?php esc_html_e( 'Migrating from another plugin? Import when you are ready—or continue with an empty library.', 'simple-urls' ); ?></p>
		&nbsp;<a href="https://support.getlasso.co/en/articles/4005802-how-to-import-link-from-another-plugin" target="_blank" class="btn btn-sm learn-btn">
			<i class="far fa-info-circle"></i> Learn
		</a>
	</div>

	<!-- IMPORT -->
	<input id="total-posts" class="d-none" value="0" />
	<section class="px-3">
		<div class="lite-container">
			<?php require_once SIMPLE_URLS_DIR . '/admin/views/import/header.php'; ?>

			<!-- LINKS TO IMPORT -->
			<div class="white-bg rounded shadow lasso-card">
				<div class="px-4 pt-4 pb-2 font-weight-bold dark-gray d-lg-block">
					<div class="row align-items-center">
						<div class="col-4">Link Title</div>
						<div class="col">Import Target</div>
						<div class="col-1">Plugin</div>
						<div class="col-1 text-center"></div>
						<div class="col-1 text-center"></div>
					</div>
				</div>

				<div id="report-content"><div class="py-5"><div class="loader"></div></div></div>

				<!-- PAGINATION (card footer) -->
				<div class="lasso-card-pager">
					<span id="lasso-link-range" class="lasso-card-pager-range" aria-live="polite"></span>
					<div class="pagination"></div>
				</div>
			</div>
		</div>

	</section>

	<!-- MODALS -->
	<?php require_once SIMPLE_URLS_DIR . '/admin/views/modals/import-all-confirm.php'; ?>
	<?php require_once SIMPLE_URLS_DIR . '/admin/views/modals/revert-all-confirm.php'; ?>
	<?php require_once SIMPLE_URLS_DIR . '/admin/views/modals/import-confirm.php'; ?>
	<?php require_once SIMPLE_URLS_DIR . '/admin/views/modals/revert-confirm.php'; ?>
	<?php require_once SIMPLE_URLS_DIR . '/admin/views/modals/url-save.php'; ?>

	<?php
	$page = $_GET['page'] ?? '';
	$template_variables = array( 'page' => $page );
	Helper::include_with_variables( SIMPLE_URLS_DIR . '/admin/assets/js/import-js.php', $template_variables, false );
	?>

	<!-- SAVE CHANGES -->
	<div class="row align-items-center mt-4">
		<div class="col-lg text-lg-right text-center">
			<button class="btn next-step"><?php esc_html_e( 'Continue without importing', 'simple-urls' ); ?> &rarr;</button>
			<div class="mt-3">
				<a href="#" class="lasso-skip-link onboarding-skip-step" data-step="import">
					<?php esc_html_e( 'Skip for now — set up later', 'simple-urls' ); ?>
				</a>
			</div>
		</div>
	</div>
</div>
