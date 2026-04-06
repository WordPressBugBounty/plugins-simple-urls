<?php
/**
 * Header HTML
 *
 * @package Header
 */

use LassoLite\Admin\Constant;

use LassoLite\Classes\Enum;
use LassoLite\Classes\Helper;
use LassoLite\Classes\Page;
use LassoLite\Classes\Setting;

$lasso_account_email   = Helper::get_option( Constant::LASSO_ACCOUNT_EMAIL, '' );
$lasso_account_user_id = intval( Helper::get_option( Constant::LASSO_ACCOUNT_USER_ID, 0 ) );
// App account only (synced email/user from hub/signup). Excludes is_connected_aff from license API; that flag is not a Lasso app login.
$is_lasso_app_account_connected = ! empty( $lasso_account_email ) || $lasso_account_user_id > 0;

$lasso_options_prefill = Setting::get_settings();
$email_support_prefill = $lasso_options_prefill[ Enum::EMAIL_SUPPORT ] ?? '';
$admin_email_prefill   = get_option( 'admin_email' );
$prefill_email_header  = ! empty( $email_support_prefill ) ? $email_support_prefill : $admin_email_prefill;

$available_pages         = Helper::available_pages();
$header_menu             = array( Enum::PAGE_DASHBOARD );
$should_show_import_step = Helper::should_show_import_page();
if ( $should_show_import_step ) {
	$header_menu[] = Enum::PAGE_IMPORT;
}
$header_menu = array_merge( $header_menu, array( Enum::PAGE_OPPORTUNITIES, Enum::PAGE_GROUPS, Enum::PAGE_TABLES ) );

if ( Setting::get_setting( 'general_disable_tooltip' ) ) {
	echo '
		<style>
			[data-tooltip]:hover:before, [data-tooltip]:hover:after {visibility: hidden;}
			i.far.fa-info-circle {display: none;}
		</style>
		';
}

?>

<script src="https://kit.fontawesome.com/21b80bd767.js" crossorigin="anonymous"></script>

<!-- REQUEST REVIEW -->
<?php
if ( Helper::show_request_review() ) {
	echo Helper::include_with_variables( SIMPLE_URLS_DIR . '/admin/views/notifications/request-review.php' );
}
?>

<!-- HEADER -->
<div class="container-fluid">
	<header class="row align-items-center purple-bg p-3 shadow">

		<!-- LASSO LOGO -->
		<div class="col-lg-2">
			<a href="<?php echo Page::get_page_url( $available_pages[ Enum::PAGE_DASHBOARD ]->slug ); ?>" class="logo mx-auto mx-lg-0">
				<img src="<?php echo SIMPLE_URLS_URL; ?>/admin/assets/images/lasso-logo.svg">
			</a>
		</div>

		<!-- NAVIGATION -->
		<div class="col-lg py-lg-0 py-3 ml-5">
			<ul class="nav justify-content-center font-weight-bold">
			<?php foreach ( $header_menu as $menu ) : ?>
				<?php $page = $available_pages[ $menu ]; ?>
				<li class="nav-item mx-3">
					<a class="nav-link px-0 white <?php echo $page->active_class; ?>" 
						href="<?php echo Page::get_page_url( $page->slug ); ?>">
						<?php echo $page->title; ?>
					</a>
				</li>
			<?php endforeach; ?>
				<li class="nav-item mx-3">
					<a class="nav-link px-0 white" href="<?php echo Constant::LASSO_ANALYTICS_URL; ?>" target="_blank">
						Analytics
					</a>
				</li>
				<li class="nav-item mx-3">
					<a class="nav-link px-0 white" href="<?php echo Constant::LASSO_AFFILIATE_PLUS_URL; ?>" target="_blank">
						Marketplace
					</a>
				</li>
			</ul>
		</div>
		<div class="col-lg-1 text-lg-right text-center pb-lg-0 pb-3 pl-1">
			<div id="wrapper-circle"></div>
		</div>
		<div class="col-lg-3 d-flex flex-column flex-lg-row align-items-center justify-content-lg-end justify-content-center pb-lg-0 pb-3 pl-1 lasso-header-actions">
			<?php if ( ! $is_lasso_app_account_connected ) : ?>
			<button type="button" class="lasso-header-account-status lasso-header-account-status--inactive mb-2 mb-lg-0 mr-lg-3 order-lg-1 order-2" data-toggle="modal" data-target="#lasso-lite-analytics-modal" title="<?php echo esc_attr( __( 'Create your free Lasso account', 'simple-urls' ) ); ?>" aria-label="<?php echo esc_attr( __( 'Account status: inactive. Create your free Lasso account.', 'simple-urls' ) ); ?>">
				<span class="lasso-header-account-status__label"><?php echo esc_html( __( 'Account status', 'simple-urls' ) ); ?></span>
				<span class="lasso-header-account-status__badge"><?php echo esc_html( __( 'Inactive', 'simple-urls' ) ); ?></span>
			</button>
			<?php endif; ?>
			<button class="btn order-lg-2 order-1 mb-2 mb-lg-0" data-toggle="modal" data-target="#url-add">
				<i class="far fa-plus-circle large-screen-only"></i> Add New Link
			</button>
		</div>

	</header>

	<!-- ALERTS -->
	<div id="lasso_lite_notifications">
	</div>

	<!-- URL ADD MODAL -->
	<?php require SIMPLE_URLS_DIR . '/admin/views/modals/url-add.php'; ?>
	<!-- Enable support modal -->
	<?php require SIMPLE_URLS_DIR . '/admin/views/modals/enable-support.php'; ?>
	<?php
	if ( ! $is_lasso_app_account_connected ) {
		echo Helper::include_with_variables(
			SIMPLE_URLS_DIR . '/admin/views/modals/lasso-lite-analytics-modal.php',
			array(
				'prefill_email_header' => $prefill_email_header,
			)
		);
	}
	?>
</div>
