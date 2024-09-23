<?php
/* @var int $dismiss */
if ( ! $dismiss ) { ?>
<?php $option_name = $option_name ?? '' ?>
<div class="notice lasso-lite-notice">
	<a class="lasso-lite-notice-dismiss" href="#" data-option-name="<?php echo $option_name?>" aria-label="Dismiss"></a>
	<div class="lasso-lite-notice-aside">
		<div class="lasso-lite-notice-icon-wrapper">
			<img width="50" src="<?php echo SIMPLE_URLS_URL; ?>/admin/assets/images/lasso-icon-brag.svg">
		</div>
		</div>
	<div class="lasso-lite-notice-content">
		<h3>Earn Higher Commission Rates and Track Revenue for FREE!</h3>
		<p>When you connect your site to Lasso, you get access to our high-commission marketplace, Amazon revenue analytics, and our customer success experts. All of that, totally free!</p>
		<div>
			<a href="/wp-admin/edit.php?post_type=surl&page=surl-dashboard&is-connect=1" class="button lasso-lite-cta1">Connect to Lasso</a>
		</div>
	</div>
</div>
<?php } ?>
