<?php

use LassoLite\Classes\Amazon_Api;
use LassoLite\Classes\Helper;
use LassoLite\Admin\Constant;

$amazon_default_tracking_country = $lasso_options['amazon_default_tracking_country'] ?? '1';
$countries_dd                    = Helper::get_countries_dd( $amazon_default_tracking_country );
$amazon_tracking_id              = $lasso_options['amazon_tracking_id'] ?? '';
$amazon_access_key_id            = $lasso_options['amazon_access_key_id'] ?? '';
$amazon_secret_key               = $lasso_options['amazon_secret_key'] ?? '';
$amazon_creators_credential_id   = $lasso_options['amazon_creators_credential_id'] ?? '';
$amazon_creators_secret          = $lasso_options['amazon_creators_secret'] ?? '';
$amazon_creators_version         = $lasso_options['amazon_creators_version'] ?? '';
$amazon_creators_partner_tag     = $lasso_options['amazon_creators_partner_tag'] ?? '';
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
						<p>Auto-Monetize Amazon Links is available with the Pro plan. <a href="<?php echo Constant::LASSO_CHECKOUT_URL_DEFAULT; ?>" target="_blank" class="purple underline">Click here to upgrade</a>.</p>
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
				<div class="white-bg rounded shadow p-4 mb-lg-0 mb-5 amazon-api-card">
					<section>
						<h3 class="d-inline-block align-middle mr-2">Amazon Product Data API</h3>
						<a href="https://support.getlasso.co/en/articles/3182308-how-to-set-up-amazon-creators-api-credentials-with-lasso" target="_blank" rel="noopener noreferrer" class="btn btn-sm learn-btn mb-2">
							<i class="far fa-info-circle"></i> Learn
						</a>

						<div class="form-group">
							<label data-tooltip="Select your Amazon Associates locale."><strong>Default Tracking ID</strong> <i class="far fa-info-circle light-purple"></i></label>
							<?php echo $countries_dd; ?>
						</div>

						<div class="amazon-paapi-fields">
							<p>If you want to use the Amazon API for product data, here's how to get your <a href="https://support.getlasso.co/en/articles/3182308-how-to-get-your-amazon-product-api-keys" target="_blank" class="purple underline">API keys from Amazon</a>.</p>
							<p>You can get Amazon product names, images, and pricing without an API key with the <a href="<?php echo Constant::LASSO_CHECKOUT_URL_DEFAULT; ?>" target="_blank" class="purple underline">Lasso Pro plan</a>.</p>

							<div class="form-group mb-4">
								<label><strong>Access Key ID</strong></label>
								<input type="text" name="amazon_access_key_id" id="amazon_access_key_id" class="form-control" value="<?php echo esc_html( $amazon_access_key_id ); ?>" placeholder="Access Key ID">
							</div>

							<div class="form-group mb-4">
								<label><strong>Secret Key</strong></label>
								<input type="text" name="amazon_secret_key" id="amazon_secret_key" class="form-control" value="<?php echo esc_html( $amazon_secret_key ); ?>" placeholder="Secret Key">
							</div>
						</div>

						<div class="amazon-creators-fields d-none">
							<p>If you want to use the Amazon Creators API for product data, here's how to get your <a href="https://support.getlasso.co/en/articles/3182308-how-to-set-up-amazon-creators-api-credentials-with-lasso" target="_blank" class="purple underline">Creators API credentials from Amazon</a>.</p>
							<p>You can get Amazon product names, images, and pricing without an API key with the <a href="<?php echo Constant::LASSO_CHECKOUT_URL_DEFAULT; ?>" target="_blank" class="purple underline">Lasso Pro plan</a>.</p>

							<div class="form-group mb-4">
								<label><strong>Credential ID</strong></label>
								<input type="password" name="amazon_creators_credential_id" id="amazon_creators_credential_id" class="form-control" value="<?php echo esc_html( $amazon_creators_credential_id ); ?>" placeholder="amzn1.application-oa2-client.xxxxx">
							</div>

							<div class="form-group mb-4">
								<label><strong>Secret</strong></label>
								<input type="password" name="amazon_creators_secret" id="amazon_creators_secret" class="form-control" value="<?php echo esc_html( $amazon_creators_secret ); ?>" placeholder="client-secret">
							</div>

							<div class="form-row">
								<div class="form-group col-12 col-md-8 mb-4">
									<label><strong>Partner tag</strong></label>
									<input type="text" name="amazon_creators_partner_tag" id="amazon_creators_partner_tag" class="form-control" value="<?php echo esc_html( $amazon_creators_partner_tag ); ?>" placeholder="yourtag-20">
								</div>

								<div class="form-group col-12 col-md-4 mb-4">
									<label><strong>Version</strong></label>
									<input type="text" name="amazon_creators_version" id="amazon_creators_version" class="form-control" value="<?php echo esc_html( $amazon_creators_version ); ?>" placeholder="3.1">
								</div>
							</div>

							<div class="form-group mb-3 text-right">
								<button type="button" class="btn btn-sm btn-lasso-lite-verify-amazon-creators">Validate</button>
							</div>
						</div>

						<div class="form-group">
							<label class="toggle m-0 mr-1">
								<input type="checkbox" class="amazon-api-mode-toggle">
								<span class="slider"></span>
							</label>
							<label class="m-0">Use Creators API</label>
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
