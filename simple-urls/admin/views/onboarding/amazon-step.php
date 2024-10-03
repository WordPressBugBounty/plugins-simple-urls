<?php

use LassoLite\Classes\Amazon_Api;
use LassoLite\Classes\Helper;

$amazon_default_tracking_country = ! $lasso_options['amazon_default_tracking_country'] ?? '1';
$countries_dd                    = Helper::get_countries_dd( $amazon_default_tracking_country );
$amazon_tracking_id              = $lasso_options['amazon_tracking_id'] ?? '';
$amazon_access_key_id            = $lasso_options['amazon_access_key_id'] ?? '';
$amazon_secret_key               = $lasso_options['amazon_secret_key'] ?? '';
$is_valid_tracking_id            = empty( $amazon_tracking_id ) ? true : Amazon_Api::validate_tracking_id( $amazon_tracking_id );

$tracking_id_class         = $is_valid_tracking_id ? '' : ' invalid-field';
$tracking_id_invalid_class = $is_valid_tracking_id ? ' d-none' : '';

$amazon_pricing_daily        = $lasso_options['amazon_pricing_daily'] ?? true;
$update_price_checked        = $amazon_pricing_daily ? 'checked' : '';
$auto_monetize_amazon        = $lasso_options['auto_monetize_amazon'] ?? true;
$auto_upgrade_eligible_links = $lasso_options['auto_upgrade_eligible_links'] ?? true;
?>

<div class="tab-item d-none" data-step="amazon">
	<div class="progressbar_container">
		<ul class="progressbar">
			<li class="step-get-started complete">Welcome</li>
			<li class="step-display-design complete" data-step="display">Display Designer</li>
			<li class="step-amazon-info active">Amazon Associates</li>
			<li class="step-connect-lasso">Connect to Lasso</li>
			<?php if ( $should_show_import_step ) : ?>
				<li class="step-import">Imports</li>
			<?php endif; ?>
		</ul>
	</div>

	<div class="onboarding_header text-center">
		<h1 class="font-weight-bold">Amazon Associates</h1>
		&nbsp;<a href="https://support.getlasso.co/en/articles/3182308-how-to-get-your-amazon-product-api-keys" target="_blank" class="btn btn-sm learn-btn">
			<i class="far fa-info-circle"></i> Learn
		</a>
	</div>

	<form class="lasso-admin-settings-form" autocomplete="off" action="">
		<!-- AMAZON -->
		<div class="row mb-5">
			<div class="col-lg">

				<div class="white-bg rounded shadow p-4 mb-4">
					<!-- AMAZON TRACKING ID -->
					<section>
						<h3>Amazon Associates Accounts</h3>
						<p>Enter your primary tracking ID and make sure your international accounts are connected with OneLink. It'll automatically send visitors to their local store.</p>

						<div class="form-group mb-4">
							<label><strong>Tracking ID for This Site</strong></label>
							<input type="text" name="amazon_tracking_id" id="amazon_tracking_id" class="form-control<?php echo $tracking_id_class; ?>" value="<?php echo $amazon_tracking_id; ?>" placeholder="tracking-20">
							<div id="tracking-id-invalid-msg" class="red<?php echo $tracking_id_invalid_class; ?>">This is an invalid Tracking ID</div>
						</div>
						<div class="form-group">
							<label class="toggle m-0 mr-1">
								<input type="checkbox" name="amazon_pricing_daily" id="amazon_pricing_daily" <?php echo $update_price_checked; ?>>
								<span class="slider"></span>
							</label>
							<label class="m-0">Update Amazon pricing daily</label>
						</div>
					</section>
				</div>
				<!-- AUTO MONETIZE AMAZON -->
				<div class="white-bg rounded shadow p-4 mb-4 pb-5">
					<section>
						<p>Auto-Monetize Amazon Links is available with the Startup plan. <br><a href="https://app.getlasso.co/checkout/startup" target="_blank" class="purple underline">Click here to upgrade</a>.</p>
						<h3>Auto-Monetize Amazon Links</h3>
						<p>Automatically monetize all current and future Amazon links with your Tracking ID and and added to your affiliate dashboard.</p>
						<p class="pt-1">
							<label class="toggle m-0 mr-1">
							<input type="checkbox" name="auto_monetize_amazon" id="auto_monetize_amazon" <?php echo $auto_monetize_amazon ? 'checked' : ''; ?>>
								<span class="slider"></span>
							</label>
							<label class="m-0">Enable Amazon Auto-Monetization</label>
						</p>
						<p class="text-danger amazon-error"></p>
					</section>
				</div>
			</div>

			<div class="col-lg">
				<div class="white-bg rounded shadow p-4 mb-lg-0 mb-5">
					<!-- PRODUCT API -->
					<section>
						<h3>Amazon Product API</h3>
						<p>If you want to use the Amazon API for product data, here's how to get your <a href="https://support.getlasso.co/en/articles/3182308-how-to-get-your-amazon-product-api-keys" target="_blank" class="purple underline">API keys from Amazon</a>.</p>
						<p>You can get Amazon product names, images, and pricing without an API key with the <a href="https://app.getlasso.co/checkout/startup" target="_blank" class="purple underline">Lasso Startup plan</a>.</p>

						<div class="form-group">
							<label data-tooltip="Select your Amazon Associates locale."><strong>Default Tracking ID</strong> <i class="far fa-info-circle light-purple"></i></label>
							<?php echo $countries_dd; ?>
						</div>

						<div class="form-group mb-4">
							<label><strong>Access Key ID</strong></label>
							<input type="text" name="amazon_access_key_id" id="amazon_access_key_id" class="form-control" value="<?php echo $amazon_access_key_id; ?>" placeholder="Access Key ID">
						</div>

						<div class="form-group mb-4">
							<label><strong>Secret Key</strong></label>
							<input type="text" name="amazon_secret_key" id="amazon_secret_key" class="form-control" value="<?php echo $amazon_secret_key; ?>" placeholder="Secret Key">
						</div>
					</section>

					<div class="form-group">
						<label class="toggle m-0 mr-1 lasso-lite-disabled no-hint">
							<input disabled type="checkbox" checked>
							<span class="slider"></span>
						</label>
						<label class="m-0 lasso-lite-disabled no-hint">Show Prime Logo In Displays</label>
					</div>

					<div class="form-group">
						<label class="toggle m-0 mr-1 lasso-lite-disabled no-hint">
							<input type="checkbox" disabled="disabled">
							<span class="slider"></span>
						</label>
						<label class="m-0 lasso-lite-disabled no-hint">Show Discount Pricing</label>
					</div>

					<div class="form-group">
						<label class="toggle m-0 mr-1">
							<input type="checkbox" name="auto_upgrade_eligible_links" id="auto_upgrade_eligible_links" <?php echo $auto_upgrade_eligible_links ? 'checked' : ''; ?>>
							<span class="slider"></span>
						</label>
						<label class="m-0">Auto-upgrade Eligible Links</label>
					</div>
				</div>
			</div>

		</div>

		<!-- SAVE CHANGES -->
		<div class="row align-items-center">
			<div class="col-lg text-lg-right text-center">
				<button type="submit" class="btn btn-save-settings-amazon" >Save and Continue &rarr;</button>
			</div>
		</div>
	</form>
</div>
