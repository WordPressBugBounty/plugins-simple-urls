<?php
/**
 * Account signup modal (Connect to Lasso). Rendered globally in the admin header when the site is not connected.
 *
 * @package Modals
 *
 * @var string $prefill_email_header Email field prefill.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!-- Account creation / connect modal -->
<div class="modal fade" id="lasso-lite-analytics-modal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 700px;">
		<div class="modal-content text-center shadow rounded p-5">
			<div class="modal-body">
				<h3 class="modal-title text-center">Connect to Lasso for Free Click Tracking</h3>
				<p class="text-center">Create your free account to access analytics, link monitoring, and more.</p>
				<div id="lasso-signup-wrapper" class="mt-3">
					<button id="btn-google-signup" class="lasso-signup-btn lasso-signup-btn-google w-100 mb-3">
						<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M18.1711 8.36788H17.4998V8.33329H9.99984V11.6666H14.7094C14.0223 13.607 12.1761 15 9.99984 15C7.23859 15 4.99984 12.7612 4.99984 10C4.99984 7.23871 7.23859 5 9.99984 5C11.2744 5 12.4344 5.48624 13.3169 6.28624L15.6744 3.92871C14.1886 2.56376 12.1948 1.66663 9.99984 1.66663C5.39775 1.66663 1.6665 5.39788 1.6665 10C1.6665 14.6021 5.39775 18.3333 9.99984 18.3333C14.6019 18.3333 18.3332 14.6021 18.3332 10C18.3332 9.44121 18.2757 8.89579 18.1711 8.36788Z" fill="#FFC107"/>
							<path d="M2.62744 6.12124L5.36536 8.12916C6.10619 6.29499 7.90036 5 9.99994 5C11.2745 5 12.4345 5.48624 13.317 6.28624L15.6745 3.92871C14.1887 2.56376 12.1949 1.66663 9.99994 1.66663C6.74494 1.66663 3.91036 3.47454 2.62744 6.12124Z" fill="#FF3D00"/>
							<path d="M10 18.3334C12.1525 18.3334 14.1084 17.4684 15.5859 16.1384L13.0034 13.9875C12.1432 14.6452 11.0865 15.0009 10 15.0009C7.83255 15.0009 5.99213 13.6175 5.29797 11.6892L2.58047 13.7559C3.84964 16.4517 6.71547 18.3334 10 18.3334Z" fill="#4CAF50"/>
							<path d="M18.1712 8.36796H17.5V8.33337H10V11.6666H14.7096C14.3809 12.5902 13.7889 13.3917 13.0021 13.9879L13.0042 13.9867L15.5867 16.1375C15.4046 16.3042 18.3333 14.1667 18.3333 10C18.3333 9.44129 18.2758 8.89587 18.1712 8.36796Z" fill="#1976D2"/>
						</svg>
						Sign Up with Google
					</button>

					<button id="btn-email-signup-toggle" class="lasso-signup-btn w-100 mb-3">
						Sign Up with Email
						<svg aria-hidden="true" focusable="false" width="6px" height="8px" viewBox="0 0 8 12" xmlns="http://www.w3.org/2000/svg">
							<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
								<g transform="translate(-148.000000, -32.000000)" fill="#FFFFFF">
									<polygon points="148 33.4 149.4 32 155.4 38 149.4 44 148 42.6 152.6 38"></polygon>
								</g>
							</g>
						</svg>
					</button>

					<div id="lasso-email-signup-form" class="dashboard-form d-none">
						<div class="lasso-signup-divider mb-3">
							<span>or</span>
						</div>

						<div class="form-group mb-3">
							<label for="lasso-signup-email" class="d-block text-left mb-2">Email</label>
							<input
								type="email"
								id="lasso-signup-email"
								class="form-control"
								placeholder="Enter your email"
								value="<?php echo esc_attr( $prefill_email_header ); ?>"
							>
							<div id="lasso-email-error" class="lasso-field-error d-none"></div>
						</div>

						<div class="form-group mb-3">
							<label for="lasso-signup-password" class="d-block text-left mb-2">Password</label>
							<div class="lasso-password-wrapper">
								<input
									type="password"
									id="lasso-signup-password"
									class="form-control"
									placeholder="Enter your password"
								>
								<button type="button" id="lasso-toggle-password" class="lasso-password-toggle">
									<svg id="lasso-eye-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
										<circle cx="12" cy="12" r="3"></circle>
									</svg>
									<svg id="lasso-eye-off-icon" class="d-none" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
										<line x1="1" y1="1" x2="23" y2="23"></line>
									</svg>
								</button>
							</div>
							<div id="lasso-password-error" class="lasso-field-error d-none"></div>
						</div>

						<div id="lasso-general-error" class="lasso-general-error d-none mb-3"></div>

						<button id="btn-create-account" class="lasso-signup-btn w-100">
							Create your account
						</button>
					</div>

					<div class="mt-3 text-center">
						<a href="#" id="lasso-skip-signup" class="lasso-skip-link" data-dismiss="modal">Skip for now</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
