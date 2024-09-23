<?php
/* @var int $dismiss */
if ( ! $dismiss ) { ?>
<?php $option_name = $option_name ?? '' ?>
<div class="notice lasso-lite-notice">
	<a class="lasso-lite-notice-dismiss" data-option-name="<?php echo $option_name?>" href="#" aria-label="Dismiss"></a>
	<div class="lasso-lite-notice-aside">
		<div class="lasso-lite-notice-icon-wrapper">
			<img width="50" src="<?php echo SIMPLE_URLS_URL; ?>/admin/assets/images/lasso-icon-brag.svg">
		</div>
	</div>
	<div class="lasso-lite-notice-content">
		<h3>Earn 3-5x Higher Amazon Commissions for Free!</h3>
		<p>Affiliate+ is Lasso's AI-powered affiliate marketplace. Earn 9-15% bonus commissions on the same products you already recommend.</p>
		<div>
			<a href="https://app.getlasso.co/signup/plus/" target="_blank" class="button lasso-lite-cta1">Join Affiliate+ for free</a>
		</div>
	</div>
</div>
<?php } ?>
