<?php
/**
 * Dashboard
 *
 * @package Dashboard
 */

use LassoLite\Classes\Config;
use LassoLite\Classes\Enum;
use LassoLite\Classes\SURL;
use LassoLite\Classes\Helper;
use LassoLite\Classes\Setting;

$dashboard_link_count = SURL::total();
$link_search_txt = esc_html( $_GET['link-search-input'] ?? '' );
$lasso_options = Setting::get_settings();
$email_support = $lasso_options[ Enum::EMAIL_SUPPORT ] ?? '';
$admin_email   = esc_attr( get_option( 'admin_email' ) );
$prefill_email = ! empty( $email_support ) ? $email_support : $admin_email;
$lasso_account_email = Helper::get_option( LassoLite\Admin\Constant::LASSO_ACCOUNT_EMAIL, '' );
$lasso_account_user_id = intval( Helper::get_option( LassoLite\Admin\Constant::LASSO_ACCOUNT_USER_ID, 0 ) );
$is_connected_aff    = intval( Helper::get_option( LassoLite\Admin\Constant::LASSO_OPTION_IS_CONNECTED_AFFILIATE, '0' ) );
$is_lasso_connected  = ! empty( $lasso_account_email ) || $lasso_account_user_id > 0 || $is_connected_aff > 0;
?>

<?php Config::get_header(); ?>

<!-- DASHBOARD -->
<input id="total-posts" class="d-none" value="0" />
<section class="px-3 pt-5 pb-0">
	<div class="lite-container min-height">

		<!-- TITLE BAR -->
		<div class="row align-items-center">

			<!-- TITLE -->
			<div class="col-lg-4 text-lg-left text-center mb-4">
				<h1 class="m-0 mr-2 d-inline-block align-middle">Dashboard</h1>
			</div>

			<!-- FILTERS -->
			<div class="col-lg text-center large mb-4 lasso-lite-skip-upsell">
				<ul class="nav justify-content-center font-weight-bold">
					<li class="nav-item mx-3 red-tooltip d-none" id="total-broken-links-li" data-tooltip="See broken URLs">
						<a class="nav-link red hover-underline px-0 lasso-lite-pro-trigger" href="#" data-feature="broken-links" id="total-broken-links-a"><span id="total-broken-links"></span></a>
					</li>
					<li class="nav-item mx-3 orange-tooltip d-none" id="total-out-of-stock-li" data-tooltip="See out-of-stock products">
						<a class="nav-link orange hover-underline px-0 lasso-lite-pro-trigger" href="#" data-feature="out-of-stock" id="total-out-of-stock-a"><span id="total-out-of-stock"></span></a>
					</li>
					<li class="nav-item mx-3 green-tooltip d-none" id="total-opportunities-li" data-tooltip="See opportunities">
						<a class="nav-link green hover-underline px-0 lasso-lite-pro-trigger" href="#" data-feature="opportunities" id="total-opportunities-a"><span id="total-opportunities"></span></a>
					</li>
				</ul>
			</div>

			<!-- SEARCH -->
			<div class="col-lg-4 mb-4">
				<form role="search" method="GET" id="links-filter" autocomplete="off">
					<div id="search-links">
						<input type="search" id="link-search-input" name="link-search-input" class="form-control"
							value="<?php echo $link_search_txt; ?>" placeholder="Search All <?php echo $dashboard_link_count; ?> Links">
					</div>
				</form>
			</div>
		</div>

		<!-- TABLE -->
		<div class="white-bg rounded shadow">

			<!-- TABLE HEADER -->
			<div class="px-4 pt-4 pb-2 font-weight-bold dark-gray d-lg-block">
				<div class="row align-items-center">
					<div class="col-lg-1 text-center">Image</div>
					<div class="col-lg-4">Title</div>
					<div class="col-lg-5">Link</div>
					<div class="col-lg-2">Groups</div>
				</div>
			</div>

			<div id="report-content"></div>
		</div>

		<!-- PAGINATION -->
		<div class="pagination row align-items-center no-gutters pb-3 pt-0 dashboard-pagination"></div>
	</div>
</section>

<!-- Lasso Lite Link Issues Snapshot Alert Box -->
<div id="lasso-lite-link-issues-snapshot-box" class="lasso-lite-link-issues-snapshot">
	<button type="button" class="close-snapshot close-link-issues-snapshot" aria-label="Close">
		<span aria-hidden="true">&times;</span>
	</button>
	<div class="snapshot-content">
		<h4 class="snapshot-title" id="link-issues-snapshot-title">Monthly Snapshot</h4>
		<div class="snapshot-message" role="list">
			<div class="snapshot-row snapshot-row-broken" role="listitem">
				<span class="snapshot-value snapshot-value-red" id="link-issues-broken-clicks">--</span>
				<span class="snapshot-label">Clicks to broken links</span>
			</div>
			<div class="snapshot-row snapshot-row-out-of-stock" role="listitem">
				<span class="snapshot-value snapshot-value-red" id="link-issues-out-of-stock-clicks">--</span>
				<span class="snapshot-label">Clicks to out-of-stock links</span>
			</div>
			<div class="snapshot-row snapshot-row-international" role="listitem">
				<span class="snapshot-value snapshot-value-orange" id="link-issues-international-clicks">--</span>
				<span class="snapshot-label">International clicks</span>
			</div>
			<div class="snapshot-row snapshot-row-earnings" id="link-issues-potential-earnings-row" role="listitem">
				<strong class="snapshot-value snapshot-value-red" id="link-issues-potential-earnings">--</strong>
				<strong class="snapshot-label">Potential earnings lost</strong>
			</div>
		</div>
		<a class="lasso-lite-upgrade-btn" href="<?php echo esc_url( LassoLite\Admin\Constant::LASSO_CHECKOUT_URL_DEFAULT ); ?>" target="_blank" rel="noopener noreferrer">
			Fix It
		</a>
	</div>
</div>

<div id="lasso-lite-click-snapshot-box" class="lasso-lite-click-snapshot">
	<button type="button" class="close-snapshot" aria-label="Close">
		<span aria-hidden="true">&times;</span>
	</button>
	<div class="snapshot-content">
		<h3 class="snapshot-title">Your Click Activity</h3>
		<p class="snapshot-message" id="snapshot-message">
			You've sent <span id="snapshot-click-count">--</span> clicks so far <span id="snapshot-period">today</span>.
		</p>
		<a
			class="learn-more-link"
			<?php if ( ! $is_lasso_connected ) : ?>
				href="#"
				data-toggle="modal"
				data-target="#lasso-lite-analytics-modal"
			<?php else : ?>
				href="<?php echo esc_url( 'https://app.getlasso.co/analytics' ); ?>"
			<?php endif; ?>
		>
			Learn More
		</a>
	</div>
</div>

<!-- Analytics Signup Modal -->
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
								value="<?php echo esc_attr( $prefill_email ); ?>"
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

<?php echo Helper::wrapper_js_render( 'dashboard-list', Helper::get_path_views_folder() . Enum::PAGE_DASHBOARD . '/list-jsrender.html' )?>

<div class="modal fade lasso-lite-pro-modal" id="lasso-lite-pro-modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="true" data-keyboard="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content text-center">
			<button type="button" class="close lasso-lite-pro-modal-close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
			<h3 class="modal-title" id="lasso-lite-pro-modal-title">Link alerts are currently disabled</h3>
			<a class="btn lasso-lite-upgrade-btn" href="https://getlasso.co/upgrade/?utm_campaign=lite-upgrade&utm_source=lasso-lite&utm_medium=wordpress" target="_blank" rel="noopener noreferrer">Enable Now</a>
		</div>
	</div>
</div>

<?php Config::get_footer(); ?>
