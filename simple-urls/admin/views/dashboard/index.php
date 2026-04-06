<?php
/**
 * Dashboard
 *
 * @package Dashboard
 */

use LassoLite\Classes\Config;
use LassoLite\Classes\Enum;
use LassoLite\Classes\Helper;
use LassoLite\Classes\Setting;
use LassoLite\Classes\SURL;

$dashboard_link_count = SURL::total();
$link_search_txt      = esc_html( $_GET['link-search-input'] ?? '' );
$lasso_account_email  = Helper::get_option( LassoLite\Admin\Constant::LASSO_ACCOUNT_EMAIL, '' );
$lasso_account_user_id = intval( Helper::get_option( LassoLite\Admin\Constant::LASSO_ACCOUNT_USER_ID, 0 ) );
// Same as header: app signup/login only; is_connected_aff alone must not skip the create-account modal.
$is_lasso_app_account_connected = ! empty( $lasso_account_email ) || $lasso_account_user_id > 0;
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
				<span id="lasso-lite-realtime-live" class="badge badge-info align-middle ml-2 d-none" title="Clicks detected while this page is open">
					Live: <span id="lasso-lite-realtime-click-count">0</span>
				</span>
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
		<?php if ( ! $is_lasso_app_account_connected ) : ?>
		<a class="learn-more-link lasso-lite-dashboard-connect-cta" href="#" role="button">
			Learn More
		</a>
		<?php endif; ?>
	</div>
</div>

<div id="lasso-lite-realtime-click-toast" class="lasso-lite-click-snapshot lasso-lite-realtime-click-toast" style="display: none;" aria-live="polite">
	<button type="button" class="close-realtime-live-toast" aria-label="Close">
		<span aria-hidden="true">&times;</span>
	</button>
	<div class="snapshot-content">
		<p class="snapshot-message" id="lasso-lite-realtime-toast-message">
			One of your products just got clicked. Create your free account to<br>
			<a class="learn-more-link lasso-lite-realtime-toast-learn-more lasso-lite-dashboard-connect-cta" href="#" role="button">
				Learn more
			</a>.
		</p>
	</div>
</div>

<?php
// Header only loads this modal when no app account; connected sites still see the realtime upsell toast.
if ( $is_lasso_app_account_connected ) {
	$lasso_options_prefill   = Setting::get_settings();
	$email_support_prefill   = $lasso_options_prefill[ Enum::EMAIL_SUPPORT ] ?? '';
	$admin_email_prefill     = get_option( 'admin_email' );
	$prefill_connect_modal   = ! empty( $email_support_prefill ) ? $email_support_prefill : $admin_email_prefill;
	echo Helper::include_with_variables(
		SIMPLE_URLS_DIR . '/admin/views/modals/lasso-lite-analytics-modal.php',
		array(
			'prefill_email_header' => $prefill_connect_modal,
		)
	);
}
?>

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
