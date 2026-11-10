<?php
use LassoLite\Classes\Helper;
?>
<div class="tab-item text-center" data-step="welcome">
	<?php
	echo Helper::include_with_variables(
		Helper::get_path_views_folder() . 'onboarding/onboarding-progressbar.php',
		array(
			'active_step'             => 'welcome',
			'should_show_import_step' => $should_show_import_step,
		)
	);
	?>

	<div class="onboarding_header container text-center">
		<h1 class="font-weight-bold"><?php esc_html_e( 'Welcome to Lasso', 'simple-urls' ); ?></h1>
		<p class="large">
			<?php esc_html_e( 'First we’ll set up a usable display with safe defaults. Amazon, account connection, and imports are optional—you can finish those later from Settings.', 'simple-urls' ); ?>
		</p>
	</div>

	<button class="btn green-bg white badge-pill px-3 shadow font-weight-bold hover-green hover-down next-step">
		<?php esc_html_e( 'Start activation', 'simple-urls' ); ?> 🚀
	</button>
</div>
