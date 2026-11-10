<?php
/**
 * Shared onboarding progress bar (required vs optional).
 *
 * @var string $active_step
 * @var bool   $should_show_import_step
 */

use LassoLite\Classes\Helper;

$progress_steps = Helper::get_onboarding_progress_steps( $should_show_import_step );
$active_index   = 0;

foreach ( $progress_steps as $index => $progress_step ) {
	if ( ( $progress_step['id'] ?? '' ) === $active_step ) {
		$active_index = $index;
		break;
	}
}
?>
<div class="progressbar_container">
	<p class="onboarding-progress-legend text-center small text-muted mb-2">
		<span class="onboarding-legend-required"><?php esc_html_e( 'Required for activation', 'simple-urls' ); ?></span>
		<span class="onboarding-legend-divider">·</span>
		<span class="onboarding-legend-optional"><?php esc_html_e( 'Optional — set up later', 'simple-urls' ); ?></span>
	</p>
	<ul class="progressbar onboarding-progressbar">
		<?php foreach ( $progress_steps as $index => $progress_step ) : ?>
			<?php
			$step_id    = $progress_step['id'];
			$classes    = array( $progress_step['progress_class'] );
			$is_active  = $step_id === $active_step;
			$is_complete = $index < $active_index;

			if ( $is_complete ) {
				$classes[] = 'complete';
			}
			if ( $is_active ) {
				$classes[] = 'active';
			}
			if ( empty( $progress_step['required'] ) ) {
				$classes[] = 'onboarding-step-optional';
			} else {
				$classes[] = 'onboarding-step-required';
			}
			?>
			<li
				class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
				data-step="<?php echo esc_attr( $step_id ); ?>"
			>
				<span class="onboarding-step-label"><?php echo esc_html( $progress_step['label'] ); ?></span>
				<?php if ( empty( $progress_step['required'] ) ) : ?>
					<span class="onboarding-step-badge"><?php esc_html_e( 'Optional', 'simple-urls' ); ?></span>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
