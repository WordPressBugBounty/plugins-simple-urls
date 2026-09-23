<?php
/**
 * Tools → Lasso health (read-only link integrity).
 *
 * @package Health
 *
 * @var array $report Link integrity report from Simple_Urls::get_link_integrity_report().
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$findings = $report['findings'] ?? array();
$summary  = $report['summary'] ?? array();
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Lasso Lite link integrity', 'simple-urls' ); ?></h1>
	<p class="description">
		<?php esc_html_e( 'Read-only health check for rewrite rules, link destinations, and import queue issues. Nothing on this page changes your links automatically.', 'simple-urls' ); ?>
	</p>

	<?php if ( empty( $findings ) && ! empty( $summary['destination_scan_complete'] ) ) : ?>
		<div class="notice notice-success">
			<p><?php esc_html_e( 'No issues detected in this check.', 'simple-urls' ); ?></p>
		</div>
	<?php elseif ( empty( $findings ) ) : ?>
		<div class="notice notice-warning">
			<p><?php esc_html_e( 'No individual issues were found, but the destination scan did not finish. Re-run this check before trusting an all-clear.', 'simple-urls' ); ?></p>
		</div>
	<?php else : ?>
		<p>
			<?php
			printf(
				/* translators: %d: number of findings */
				esc_html__( '%d finding(s) reported.', 'simple-urls' ),
				intval( $summary['total'] ?? count( $findings ) )
			);
			?>
		</p>
		<table class="widefat striped">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Category', 'simple-urls' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Issue', 'simple-urls' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Repair guidance', 'simple-urls' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $findings as $finding ) : ?>
					<tr>
						<td><code><?php echo esc_html( $finding['category'] ?? '' ); ?></code></td>
						<td><?php echo esc_html( $finding['message'] ?? '' ); ?></td>
						<td><?php echo esc_html( $finding['guidance'] ?? '' ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
