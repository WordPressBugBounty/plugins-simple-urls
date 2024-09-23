<?php

use LassoLite\Classes\Setting;

$lasso_options = Setting::get_settings();
?>

<div id="activate" class="tab-item text-center d-none" data-step="connect-lasso">
	<div class="progressbar_container">
		<ul class="progressbar">
			<li class="step-get-started complete">Welcome</li>
			<li class="step-display-design complete" data-step="display">Display Designer</li>
			<li class="step-amazon-info complete" data-step="amazon">Amazon Associates</li>
			<li class="step-connect-lasso active" data-step="connect-lasso">Connect to Lasso</li>
			<?php if ( $should_show_import_step ) : ?>
				<li class="step-import">Imports</li>
			<?php endif; ?>
		</ul>
	</div>

	<h1 class="font-weight-bold">Connect Your Lasso Account</h1>
	<p>Adding your Lasso license key enables important features like Affiliate+ opportunities and link synchronization.</p>
	<p>If you don’t have a Lasso license key, you can get one <a href="https://app.getlasso.co/checkout/startup" target="_blank">here.</a></p>
	<div class="form-group mb-4">
		<div class="collapse orange" id="activate-error"><label>This license key doesn't work. Double-check and try again.</label></div>
		<input type="text" name="license_serial" class="form-control w-50 d-block-center" id="license" value="<?php echo $lasso_options['license_serial']; ?>" placeholder="Enter your license key">
	</div>

	<button id="activate-license" class="btn green-bg white badge-pill px-3 shadow font-weight-bold hover-green hover-down mb-3">
		Activate Lasso
	</button>
	<div class="mb-3">
		<a href="#" class="underline next-step">Continue without a license key</a>
	</div>
</div>
