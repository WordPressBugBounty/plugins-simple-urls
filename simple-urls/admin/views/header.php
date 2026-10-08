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

<script>
	// Broken thumbnails (deleted media, missing uploads on a restored backup) show the default image instead.
	// Capture phase: image error events do not bubble. The flag stops a loop if the default is missing too.
	document.addEventListener( 'error', function ( e ) {
		var img = e.target;
		if ( ! img || 'IMG' !== img.tagName || img.dataset.lassoLiteThumbFallback ) {
			return;
		}
		img.dataset.lassoLiteThumbFallback = '1';
		img.src = '<?php echo esc_url( Constant::DEFAULT_THUMBNAIL ); ?>';
	}, true );
</script>

<!-- REQUEST REVIEW -->
<?php
if ( Helper::show_request_review() ) {
	echo Helper::include_with_variables( SIMPLE_URLS_DIR . '/admin/views/notifications/request-review.php' );
}
?>

<!-- HEADER -->
<?php // #1078/#1081: one full-height row — logo · nav · progress · CTA (Add New Link + Inactive chip under it). <1280px: nav collapses behind menu. ?>
<div class="container-fluid">
	<header class="lasso-lite-header purple-bg shadow">

		<!-- LASSO LOGO -->
		<a href="<?php echo Page::get_page_url( $available_pages[ Enum::PAGE_DASHBOARD ]->slug ); ?>" class="logo">
			<img src="<?php echo SIMPLE_URLS_URL; ?>/admin/assets/images/lasso-logo.svg" alt="Lasso">
		</a>

		<!-- NAVIGATION -->
		<div id="lasso-lite-header-nav" class="collapse">
			<ul id="lasso-header-nav" class="nav justify-content-center font-weight-bold lasso-lite-header-nav">
			<?php foreach ( $header_menu as $menu ) : ?>
				<?php $page = $available_pages[ $menu ]; ?>
				<li class="nav-item">
					<a class="nav-link px-0 white <?php echo $page->active_class; ?>"
						href="<?php echo Page::get_page_url( $page->slug ); ?>">
						<?php echo $page->title; ?>
					</a>
				</li>
			<?php endforeach; ?>
				<li class="nav-item">
					<a class="nav-link px-0 white" href="<?php echo Constant::LASSO_ANALYTICS_URL; ?>" target="_blank">
						Analytics <i class="far fa-external-link-alt lasso-lite-header-ext" aria-hidden="true"></i>
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link px-0 white" href="<?php echo Constant::LASSO_AFFILIATE_PLUS_URL; ?>" target="_blank">
						Marketplace <i class="far fa-external-link-alt lasso-lite-header-ext" aria-hidden="true"></i>
					</a>
				</li>
			</ul>
		</div>

		<!-- UTILITIES: progress · CTA (Add New Link + inactive chip) · menu toggle -->
		<div class="lasso-lite-header-utils">
			<div id="wrapper-circle"></div>
			<div class="lasso-lite-header-cta">
				<button id="lasso-header-add" type="button" class="btn" data-toggle="modal" data-target="#url-add">
					<i class="far fa-plus-circle" aria-hidden="true"></i> Add New Link
				</button>
				<?php if ( ! $is_lasso_app_account_connected ) : ?>
				<div class="lasso-lite-account-chip">
					<button type="button" class="lasso-header-account-status lasso-header-account-status--inactive" data-toggle="modal" data-target="#lasso-lite-analytics-modal" aria-haspopup="dialog" aria-controls="lasso-lite-account-popover" aria-label="<?php echo esc_attr( __( 'Account status: inactive. Create your free Lasso account.', 'simple-urls' ) ); ?>">
						<span class="lasso-header-account-status__dot" aria-hidden="true"></span>
						<span class="lasso-header-account-status__label"><?php echo esc_html( __( 'Account', 'simple-urls' ) ); ?></span>
						<span class="lasso-header-account-status__badge"><?php echo esc_html( __( 'Inactive', 'simple-urls' ) ); ?></span>
					</button>
					<div class="lasso-lite-account-popover" id="lasso-lite-account-popover" role="region" aria-labelledby="lasso-lite-account-popover-heading">
						<p class="lasso-lite-account-popover__heading" id="lasso-lite-account-popover-heading"><?php echo esc_html( __( "Your Lasso account isn't connected", 'simple-urls' ) ); ?></p>
						<p class="lasso-lite-account-popover__body"><?php echo esc_html( __( "This site isn't linked to a free Lasso account yet, so click analytics and link alerts are off.", 'simple-urls' ) ); ?></p>
						<button type="button" class="btn btn-sm lasso-lite-account-popover__cta" data-toggle="modal" data-target="#lasso-lite-analytics-modal">
							<?php echo esc_html( __( 'Connect free account', 'simple-urls' ) ); ?>
						</button>
					</div>
				</div>
				<?php endif; ?>
			</div>
			<!-- MENU TOGGLE (#1066 + #1078: below 1280px — opens #lasso-lite-header-nav) -->
			<button type="button" class="lasso-lite-header-toggle white" data-toggle="collapse" data-target="#lasso-lite-header-nav" aria-controls="lasso-lite-header-nav" aria-expanded="false" aria-label="<?php echo esc_attr__( 'Menu', 'simple-urls' ); ?>">
				<i class="far fa-bars" aria-hidden="true"></i>
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
