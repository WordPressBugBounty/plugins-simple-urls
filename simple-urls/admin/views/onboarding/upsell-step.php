<?php

use LassoLite\Classes\Enum;
use LassoLite\Classes\Page;
?>

<div class="tab-item d-none" data-step="upsell">
	<div class="container-upsell">
		<img src="<?php echo SIMPLE_URLS_URL; ?>/admin/assets/images/upsell-desktop.png" class="upsell-img img-fluid d-none d-md-block">
		<img src="<?php echo SIMPLE_URLS_URL; ?>/admin/assets/images/upsell-mobile.png" class="upsell-img img-fluid d-md-none d-block">
	</div>

	<div class="mb-3 text-center">
		<h1 class="font-weight-bold green">Unlock Explosive Growth With <br>API-Free Amazon Data and Pro Features</h1>
	</div>
	<div class="onboarding_header text-center">
		<h1 class="font-weight-bold black">Just $15 Per Month</h1>
	</div>

	<div class="row feature-list" style="margin:0 auto">
		<div class="col-md-6 col-12">
			<ul id="plan-1-feature-list" class="plan-feature-list pt-3">
				<li class="font-weight-bold">API-free Amazon Product Images & Prices</li>
				<li class="font-weight-bold">Revenue Analytics for Major Networks</li>
				<li class="font-weight-bold">AI Opportunity Alerts</li>
			</ul>
		</div>
		<div class="col-md-6 col-12">
			<ul id="plan-2-feature-list" class="plan-feature-list pt-3">
				<li class="font-weight-bold">Access 9-15% Amazon Bonuses</li>
				<li class="font-weight-bold">Broken Link and Out-of-Stock Alerts</li>
				<li class="font-weight-bold">Priority Support</li>
			</ul>
		</div>
	</div>

	<a href="https://app.getlasso.co/checkout/startup" id="upgrade-license" class="btn green-bg white badge-pill px-3 shadow font-weight-bold hover-green hover-down mb-3 mt-4 d-block-center">
		<i class="fas fa-crown white mr-2"></i>Just $15 Per Month<i class="fas fa-crown white ml-2"></i>
	</a>
	<div class="mt-2">
		<a href="<?php echo esc_url( Page::get_lite_page_url( Enum::PAGE_DASHBOARD ) ); ?>" class="underline next-step text-center d-block-center">No thanks, take me to the dashboard</a>
	</div>
</div>
