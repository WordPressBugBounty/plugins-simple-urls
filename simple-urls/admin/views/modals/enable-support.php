<?php
/**
 * Enable Support
 *
 * @package Enable Support Modal
 */

use LassoLite\Classes\Enum;
use LassoLite\Classes\Setting;

$settings = Setting::get_settings();
$email_support = ! empty( $settings[Enum::EMAIL_SUPPORT] ) ? $settings[Enum::EMAIL_SUPPORT] : get_option( 'admin_email' );
$is_subscribe_setting = $settings[Enum::IS_SUBSCRIBE] ?? '';
$is_subscribe_setting_checked = 'true' === $is_subscribe_setting || empty( $settings[Enum::IS_SUBSCRIBE] ) ? 'checked' : '';
?>
<div class="modal fade" id="enable-support" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content shadow p-5 rounded text-center">
			<div id="enable-support-wrapper">
				<h2>Connect to Lasso</h2>
				<div class="mb-4">
					<ul class="checkmarks-list pt-3 pl-5">
						<li class="d-flex font-weight-bold text-left">Get access to 3-5x payouts on top of Amazon Associates.</li>
						<li class="d-flex font-weight-bold text-left">Connect with our team for support</li>
					</ul>
				</div>
				<div class="text-center">
					<p><a href="https://app.getlasso.co/signup/plus" target="_blank" id="btn-save-support" class="btn">Connect for free</a></p>
					<div class="clearfix"></div>
					<div class="clearfix"></div>
					<small class="mt-2 dismiss">No thanks.</small>
				</div>
			</div>
		</div>
	</div>
</div>