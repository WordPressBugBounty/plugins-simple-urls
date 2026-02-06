<?php
/**
 * Footer HTML
 *
 * @package Footer
 */
use LassoLite\Admin\Constant;

use LassoLite\Classes\Enum;
use LassoLite\Classes\Helper;
use LassoLite\Classes\License;
use LassoLite\Classes\Page;
use LassoLite\Classes\Setting;

$current_page     = $_GET['page'] ?? '';
$import_page_slug = Helper::add_prefix_page( Enum::PAGE_IMPORT );
$ajax_url         = admin_url( 'admin-ajax.php' );

$user_email = get_option( 'admin_email' ); // phpcs:ignore
$user_email = get_option( 'lasso_license_email', $user_email );

$settings                     = Setting::get_settings();
$support_enable               = $settings[ Enum::SUPPORT_ENABLED ] ?? 0;
$general_disable_notification = (bool) $settings['general_disable_notification'];
$customer_flow_enabled        = (int) $settings[ Enum::CUSTOMER_FLOW_ENABLED ];

$lasso_lite_setting = new Setting();
$plugins_for_import = $lasso_lite_setting->check_plugins_for_import();
$import_page_link   = Page::get_lite_page_url( Enum::PAGE_IMPORT );
$license_active     = License::get_license_status();
$is_connected_aff   = intval( Helper::get_option( Constant::LASSO_OPTION_IS_CONNECTED_AFFILIATE, '0' ) );
$is_show_upsell     = ! $license_active && 0 === $is_connected_aff;
$intercom_jwt       = $settings[ Enum::INTERCOM_JWT ] ?? '';

// Normalize email before Intercom usage.
$user_email = strtolower( trim( $user_email ) );

?>
<input type="hidden" id="license_status" value="<?php echo $license_active; ?>">

<?php
if ( ! $lasso_lite_setting->is_setting_onboarding_page() ) {
	$is_connected_aff      = intval( Helper::get_option( Constant::LASSO_OPTION_IS_CONNECTED_AFFILIATE, '0' ) );
	$lasso_account_email   = Helper::get_option( Constant::LASSO_ACCOUNT_EMAIL, '' );
	$lasso_account_user_id = intval( Helper::get_option( Constant::LASSO_ACCOUNT_USER_ID, 0 ) );
	$is_lasso_connected    = ! empty( $lasso_account_email ) || $lasso_account_user_id > 0 || $is_connected_aff > 0;
	$prefill_email         = get_option( 'admin_email' );

	$lasso_logo_url = esc_url( rtrim( SIMPLE_URLS_URL, '/' ) . '/admin/assets/images/lasso-logo.svg' );
	$footer_links   = array(
		array(
			'label' => 'About',
			'href'  => 'https://getlasso.co/about/',
		),
		array(
			'label' => 'Help Center',
			'href'  => 'https://support.getlasso.co/',
		),
		array(
			'label' => 'Go Pro',
			'href'  => Constant::LASSO_UPGRADE_URL,
		),
	);

	$render_footer_brand_social = function () use ( $lasso_logo_url ) {
		?>
		<div class="lasso-footer-brand">
			<a href="https://getlasso.co" target="_blank" rel="noopener">
				<img src="<?php echo $lasso_logo_url; ?>" alt="Lasso Logo" class="lasso-footer-logo">
			</a>
			<div class="lasso-footer-social">
				<a href="https://twitter.com/lassowp" target="_blank" rel="noopener nofollow" title="Twitter">
					<i class="fa-brands fa-twitter"></i>
				</a>
				<a href="https://www.linkedin.com/company/lasso-analytics/" target="_blank" rel="noopener nofollow" title="LinkedIn">
					<i class="fa-brands fa-linkedin-in"></i>
				</a>
				<a href="https://www.instagram.com/getlassoco/" target="_blank" rel="noopener nofollow" title="Instagram">
					<i class="fa-brands fa-instagram"></i>
				</a>
				<a href="https://www.youtube.com/@getlasso" target="_blank" rel="noopener nofollow" title="YouTube">
					<i class="fa-brands fa-youtube"></i>
				</a>
			</div>
		</div>
		<?php
	};

	$render_footer_links = function ( $link_class = '' ) use ( $footer_links ) {
		foreach ( $footer_links as $link ) {
			$href  = esc_url( $link['href'] );
			$label = esc_html( $link['label'] );
			?>
			<a <?php echo $link_class ? 'class="' . esc_attr( $link_class ) . '"' : ''; ?> href="<?php echo $href; ?>" target="_blank" rel="noopener">
				<?php echo $label; ?>
			</a>
			<?php
		}
	};
	?>

	<footer class="lasso-plugin-footer <?php echo $is_lasso_connected ? 'lasso-plugin-footer--default-only' : 'lasso-plugin-footer--with-cta'; ?>">
		<div class="lasso-plugin-footer-version">
			<?php print 'Version ' . LASSO_LITE_VERSION; // phpcs:ignore ?>
		</div>

		<?php if ( ! $is_lasso_connected ) : ?>
		<div class="lasso-footer-cta-content">
			<div class="lasso-plugin-footer-version">
				<?php print 'Version ' . LASSO_LITE_VERSION; // phpcs:ignore ?>
			</div>

			<div class="lasso-footer-cta">
				<div class="lasso-footer-cta-inner">
					<h2 class="lasso-footer-cta-title">Create Your Free Account</h2>
					<p class="lasso-footer-cta-subtitle">Access free click tracking, private brand deals, and more.</p>

					<div id="lasso-signup-wrapper" class="lasso-footer-signup-wrapper">
						<button id="btn-google-signup" class="lasso-signup-btn lasso-signup-btn-google w-100 mb-3">
							<svg width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
								<path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
								<path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
								<path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
								<path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
							</svg>
							Sign Up with Google
						</button>

						<button id="btn-email-signup-toggle" class="lasso-signup-btn w-100 mb-3">
							Sign Up with Email
							<svg aria-hidden="true" focusable="false" width="6px" height="8px" viewBox="0 0 8 12" xmlns="http://www.w3.org/2000/svg">
								<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
									<g transform="translate(-148.000000, -32.000000)" fill="#7c3aed">
										<polygon points="148 33.4 149.4 32 155.4 38 149.4 44 148 42.6 152.6 38"></polygon>
									</g>
								</g>
							</svg>
						</button>

						<div id="lasso-email-signup-form" class="footer-form d-none">
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
					</div>

					<div class="lasso-footer-cta-footer">
						<?php $render_footer_brand_social(); ?>
						<?php $render_footer_links( 'lasso-footer-nav-link' ); ?>
					</div>
				</div>
			</div>
		</div>

		<input type="hidden" id="footer-prefill-email" value="<?php echo esc_attr( $prefill_email ); ?>">
		<input type="hidden" id="footer-lasso-hub-url" value="<?php echo esc_attr( Constant::LASSO_HUB_URL ); ?>">
		<?php endif; ?>

		<?php if ( $is_lasso_connected ) : ?>
		<div class="lasso-footer-default">
			<div class="lasso-footer-inner">
				<?php $render_footer_brand_social(); ?>
				<div class="lasso-footer-links">
					<?php $render_footer_links(); ?>
				</div>
			</div>
		</div>
		<?php endif; ?>
	</footer>
<?php } ?>

<script>
	// Check existing account via admin-ajax so it's visible in DevTools Network.
	window.lassoLiteCheckExistingAccount = function () {
		try {
			if (window.__lassoLiteExistingAccountCheckRan) return;
			window.__lassoLiteExistingAccountCheckRan = true;

			if (!window.lassoLiteOptionsData || !lassoLiteOptionsData.ajax_url) return;
			if (!lassoLiteOptionsData.optionsNonce) return;

			// Only run when we don't already have a synced user_id.
			var userId = parseInt((lassoLiteOptionsData.userId || 0), 10) || 0;
			if (userId > 0) return;

			jQuery.ajax({
				url: lassoLiteOptionsData.ajax_url,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'lasso_lite_check_existing_account',
					nonce: lassoLiteOptionsData.optionsNonce
				}
			});
		} catch (e) {}
	};

	jQuery(function () {
		window.lassoLiteCheckExistingAccount();
	});
</script>

<div class="modal fade" id="modal-save-animation" data-backdrop="static" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content p-5 shadow text-center">
			<h3></h3>
			<p>Saving your changes now.</p>
			<div class="progress">
				<div class="progress-bar progress-bar-striped progress-bar-animated green-bg" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 75%"></div>
			</div>
		</div>
	</div>
</div>

<div id="up-sell-modal">
	<p class="large">This is a premium feature.</p>
	<a href="<?php echo esc_url( Constant::LASSO_UPGRADE_URL ); ?>" target="_blank">Upgrade to Lasso Pro</a>
</div>

<?php if ( ! $lasso_lite_setting->is_setting_onboarding_page() && empty( $intercom_jwt ) ) { ?>
	<div id="support-launcher">
		<i class="fas fa-question icon-default"></i>
		<i class="fas fa-times icon-close"></i>
	</div>
<?php } ?>

<?php
	echo Helper::wrapper_js_render( 'import-suggestion-jsrender', Helper::get_path_views_folder() . 'notifications/import-suggestion-jsrender.html' );
?>
<?php
	echo Helper::wrapper_js_render( 'earnings-notification-jsrender', Helper::get_path_views_folder() . 'notifications/earnings-notification-jsrender.html' );
?>

<!-- MODALS -->
<?php
if ( ! $customer_flow_enabled ) {
	require_once SIMPLE_URLS_DIR . '/admin/views/modals/customer-flow-confirm.php';
}
?>

<?php
	// ? Show notification for import plugin
if ( '' !== $plugins_for_import && ! $general_disable_notification && $import_page_slug !== $current_page && Helper::is_importable() ) :
	?>
	<script>
		let json_data = [{ import_page_link: '<?php echo $import_page_link; ?>' }];
		lasso_lite_helper.inject_to_template(jQuery("#lasso_lite_notifications"), 'import-suggestion-jsrender', json_data);
	</script>
<?php endif; ?>

<?php
$template_path = '';
if ( $is_show_upsell ) {
	if ( ! $support_enable && ! $lasso_lite_setting->is_setting_onboarding_page() ) {
		$template_path = '/admin/views/notifications/promotions-no-intercom-jsrender.html';
	} else {
		$template_path = '/admin/views/notifications/promotions-intercom-jsrender.html';
	}
}

?>

<?php if ( 0 !== intval( Helper::get_option( Constant::LASSO_OPTION_AFFILIATE_PROMOTIONS, '1' ) ) && $is_show_upsell && '' !== $template_path ) : ?>
<script>
	var notificationHtml = `<?php include SIMPLE_URLS_DIR . $template_path; ?>`;
	jQuery("#lasso_lite_notifications").append(notificationHtml);
	jQuery('#lasso-intercom-promotions button.close').click(function() {
		let btn = jQuery(this);
		jQuery.ajax({
			url: '<?php echo $ajax_url; // phpcs:ignore ?>',
			type: 'post',
			data: {
				action: 'lasso_lite_disable_affiliate_promotions',
				nonce: lassoLiteOptionsData.optionsNonce,
			},
		}).done(function(res) {
			jQuery('#lasso-intercom-promotions').collapse('hide');
		});
	});
	
	// ? Redirect to Lasso dashboard and show modal connect to Lasso
	if (window.location.href.indexOf( 'post_type=surl&page=surl-dashboard&is-connect=1' ) !== -1) {
		jQuery("#enable-support").modal('show');
		var newUrl = window.location.href.replace('&is-connect=1', '');
		window.history.replaceState(null, null, newUrl);
	}
</script>
<?php endif; ?>

<?php if ( ! $lasso_lite_setting->is_setting_onboarding_page() && ! $is_lasso_connected ) : ?>
<script>
	jQuery(function () {
		try {
			var now = new Date();
			var monthKey = now.getFullYear() + '-' + (now.getMonth() + 1);
			var dismissedMonth = localStorage.getItem('lasso_lite_earnings_notification_dismissed_month');
			if (dismissedMonth === monthKey) return;

			if (!window.lassoLiteOptionsData || !lassoLiteOptionsData.ajax_url) return;
			if (!lassoLiteOptionsData.optionsNonce) return;

			var useCache = '1';
			try {
				var url = new URL(window.location.href);
				useCache = (url.searchParams.get('use_cache') || '1');
			} catch (e) {}

			jQuery.ajax({
				url: lassoLiteOptionsData.ajax_url,
				type: 'post',
				dataType: 'json',
				data: {
					action: 'lasso_lite_get_earnings_estimate',
					nonce: lassoLiteOptionsData.optionsNonce,
					use_cache: useCache
				}
			}).done(function (res) {
				if (!res || !res.success || !res.data) return;
				var data = res.data.data ? res.data.data : res.data;

				var tplData = {
					earnings_display: data.payout_30d,
					signup_url: '<?php echo esc_js( Constant::LASSO_PLUS_SIGNUP_URL ); ?>'
				};

				lasso_lite_helper.inject_to_template(
					jQuery("#lasso_lite_notifications"),
					'earnings-notification-jsrender',
					tplData,
					false
				);
				jQuery('#lasso-earnings-notification').collapse('show');
			});

			jQuery(document).on('click', '#lasso-earnings-notification button.close', function(e) {
				try {
					localStorage.setItem('lasso_lite_earnings_notification_dismissed_month', monthKey);
				} catch (err) {}
				jQuery('#lasso-earnings-notification').collapse('hide');
			});
		} catch (e) {}
	});
</script>
<?php endif; ?>

<!-- JS errors detection -->
<script
	src="https://browser.sentry-cdn.com/7.9.0/bundle.tracing.min.js"
	integrity="sha384-a80B6QRSQ+pPpoX+H79BVaE52KTvYkQDL+lD8+TajwMxswO+ywB3p99gWNraTNrt"
	crossorigin="anonymous"
></script>

<script>
	var license_active = '<?php echo $license_active ? 1 : 0; // phpcs:ignore ?>';
	let lasso_path = '<?php echo SIMPLE_URLS_URL; // phpcs:ignore ?>';
	let post_type = 'post_type=<?php echo SIMPLE_URLS_SLUG; // phpcs:ignore ?>';
	Sentry.init({
		dsn: '<?php echo Constant::SENTRY_DSN; // phpcs:ignore ?>',
		release: '<?php echo LASSO_LITE_VERSION; // phpcs:ignore ?>',
		ignoreErrors: [
			'ResizeObserver loop limit exceeded',
			'ResizeObserver loop completed with undelivered notifications',
			'__ez is not defined',
			'_ezaq is not defined',
			'Can\'t find variable: _ezaq',
			'wpColorPickerL10n is not defined',
			'window.jQuery(...).wpColorPicker is not a function',
		],
		integrations: [new Sentry.Integrations.BrowserTracing()],
		tracesSampleRate: 1.0,
		beforeSend(event, hint) {
			try {
				let is_lasso_lite_error = false;
				let event_id = event.event_id;
				let frames = event.exception.values[0].stacktrace.frames;
				for(let i = 0; i < frames.length; i++) {
					if(frames[i].filename.includes(lasso_path) || frames[i].filename.includes(post_type)) {
						is_lasso_lite_error = true;
						break;
					}
				}

				if(is_lasso_lite_error) {
					return event;
				}
			} catch (error) {
				console.log(error);
			}
		}
	});

	Sentry.configureScope(function(scope) {
		scope.setUser({
			email: '<?php echo $user_email; ?>',
		});
		scope.setTag('site_id', '<?php echo Helper::get_option( Constant::SITE_ID_KEY ); ?>');
		scope.setTag('wp_version', '
		<?php
		global $wp_version;
		echo esc_js( $wp_version );
		?>
		');
	});

</script>
<script>
	// Expose APP_ID and a reusable loader so other scripts can init Intercom after AJAX
	var APP_ID = '<?php echo Constant::LASSO_INTERCOM_APP_ID; // phpcs:ignore ?>';
	window.lassoInitIntercom = function(intercomParams, options) {
		try {
			// Ensure app_id always present in settings
			if (!intercomParams) intercomParams = {};
			if (!intercomParams.app_id) intercomParams.app_id = APP_ID;
			window.intercomSettings = intercomParams;
			var shouldShow = !options || options.show !== false;
			var onReady = function() {
				try {
					window.Intercom('reattach_activator');
					window.Intercom('update', window.intercomSettings);
					if (shouldShow) {
						window.Intercom('show');
					}
				} catch (e) {}
			};
			if (typeof window.Intercom === 'function') {
				onReady();
				return;
			}
			var s = document.createElement('script');
			s.type = 'text/javascript';
			s.async = true;
			s.src = 'https://widget.intercom.io/widget/' + APP_ID;
			s.onload = onReady;
			var x = document.getElementsByTagName('script')[0];
			x.parentNode.insertBefore(s, x);
		} catch (e) {}
	}
</script>
<!-- PHP errors detection -->
<?php
echo Helper::wrapper_js_render( 'setup-pregress-jsrender', Helper::get_path_views_folder() . 'components/setup-progress-jsrender.html' );
?>
<?php
if ( $intercom_jwt && ! $lasso_lite_setting->is_setting_onboarding_page() ) {
	$intercom_user_id = Helper::get_intercom_user_id( $user_email, $intercom_jwt );
	$user             = get_user_by( 'email', $user_email );
	$user_name        = isset( $user->display_name ) ? $user->display_name : get_bloginfo( 'name' );
	$classic_editor   = Helper::is_classic_editor() ? 1 : 0;
	$email_support    = $settings[ Enum::EMAIL_SUPPORT ] ?? $user_email;
	$email_support    = strtolower( trim( $email_support ) );
	$user_hash        = $settings[ Enum::USER_HASH ] ?? '';
	$lasso_lite_user  = 1;
	if ( Helper::is_lasso_pro_plugin_active() ) {
		$lasso_lite_user = 0;
	}
	?>

<script>
	var isClassicEditor = '<?php echo $classic_editor; // phpcs:ignore ?>' == 1 ? true : false;
	var lasso_lite_user = '<?php echo $lasso_lite_user; // phpcs:ignore ?>' == 1 ? true : false;
	var intercomParams = {
		app_id: APP_ID,
		name: '<?php echo addslashes( $user_name ); // phpcs:ignore ?>',
		user_id: '<?php echo esc_js( $intercom_user_id ); // phpcs:ignore ?>',
		email: '<?php echo esc_js( $email_support ); // phpcs:ignore ?>',
		lasso_version: parseInt('<?php echo LASSO_LITE_VERSION; // phpcs:ignore ?>'),
		classic_editor: isClassicEditor,
		wp_admin_url: '<?php echo admin_url(); // phpcs:ignore ?>',
		lasso_lite_user: lasso_lite_user,
		intercom_user_jwt: '<?php echo $intercom_jwt; ?>'
	};
	window.lassoInitIntercom(intercomParams, { show: false });
</script>
<?php } ?>
