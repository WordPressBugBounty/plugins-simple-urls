<?php
/**
 * Dormant Lite re-engage admin notice (zero links).
 *
 * @package Simple_Urls
 *
 * @var string $option_name Dismiss option key.
 * @var string $cta_url     First-link CTA URL.
 *
 * Do not add WP class `notice`: lasso-dashboard.css hides `.notice { display: none !important }` on Lasso admin screens.
 */

$option_name = $option_name ?? '';
$cta_url     = $cta_url ?? '';
?>
<div class="lasso-lite-notice lasso-lite-dormant-reengage-notice">
	<a class="lasso-lite-notice-dismiss" href="#" data-option-name="<?php echo esc_attr( $option_name ); ?>" aria-label="<?php esc_attr_e( 'Dismiss', 'simple-urls' ); ?>"></a>
	<div class="lasso-lite-notice-aside">
		<div class="lasso-lite-notice-icon-wrapper">
			<img width="50" src="<?php echo esc_url( SIMPLE_URLS_URL . '/admin/assets/images/lasso-icon-brag.svg' ); ?>" alt="">
		</div>
	</div>
	<div class="lasso-lite-notice-content">
		<h3><?php esc_html_e( 'Create your first Lasso link', 'simple-urls' ); ?></h3>
		<p><?php esc_html_e( 'You have not created a Lasso link yet. Create your first link to start tracking and earning.', 'simple-urls' ); ?></p>
		<div>
			<a href="<?php echo esc_url( $cta_url ); ?>" class="button lasso-lite-cta1"><?php esc_html_e( 'Create your first link', 'simple-urls' ); ?></a>
		</div>
	</div>
</div>
