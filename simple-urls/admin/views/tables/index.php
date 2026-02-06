<?php
/**
 * Tables
 *
 * @package Tables
 */

use LassoLite\Classes\Config;
use LassoLite\Admin\Constant;
?>
<?php Config::get_header(); ?>

<!-- OPPORTUNITIES -->
<input id="total-posts" class="d-none" value="0" />
<section class="px-3 py-5">
	<div class="lite-container text-center lasso-lite-pro-page">

		<!-- TITLE BAR -->
		<div class="align-items-center">

			<!-- TITLE -->
			<div class="mb-4">
				<h1 class="m-0 mr-2 align-middle">Tables</h1>
			</div>
		</div>
		<div class="align-items-center">
			
			<p class="large">Our tables perform exceptionally well in top-of-funnel content AND the alternatives section of reviews.</p>
			
			<p class="large">Tables are available with Lasso Pro. <a href="<?php echo esc_url( Constant::LASSO_UPGRADE_URL ); ?>" target="_blank">Click here to upgrade</a>.</p>
			
			<div class="text-center">
				<a href="https://getlasso.co/features/displays/#comparison-tables" target="_blank">
					<img src="<?php echo SIMPLE_URLS_URL; ?>/admin/assets/images/tables-thumbnail.png" style="max-width: 800px;">
				</a>
			</div>
		</div>
		
	</div>	
</section>
		
<div class="modal fade lasso-lite-pro-modal" id="lasso-lite-pro-modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="true" data-keyboard="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content text-center">
			<button type="button" class="close lasso-lite-pro-modal-close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
			<h3 class="modal-title">Tables are currently disabled</h3>
			<a class="btn lasso-lite-upgrade-btn" href="https://getlasso.co/upgrade/?utm_campaign=lite-upgrade&utm_source=lasso-lite&utm_medium=wordpress" target="_blank" rel="noopener noreferrer">Enable Now</a>
		</div>
	</div>
</div>


<?php Config::get_footer(); ?>
