<?php
/**
 * Declare class Lasso_Process_Update_Amazon
 *
 * @package Lasso_Process_Update_Amazon
 */

namespace LassoLite\Classes\Processes;

use LassoLite\Classes\Amazon_Api;
use LassoLite\Classes\Processes\Process;

use LassoLite\Models\Amazon_Products;

/**
 * Lasso_Process_Update_Amazon
 */
class Amazon extends Process {
	/**
	 * Action name
	 *
	 * @var string $action
	 */
	protected $action = 'lassolite_amazon_process';

	/**
	 * Log name
	 *
	 * @var string $log_name
	 */
	protected $log_name = 'update_amazon';

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $product_id Queue item to iterate over.
	 *
	 * @return mixed
	 */
	public function task( $product_id ) {
		if ( empty( $product_id ) ) {
			return false;
		}

		$this->update_amazon_pricing( $product_id );
		$this->set_processing_runtime();

		return false;
	}

	/**
	 * Whether this request should drain the Amazon queue inline after dispatch.
	 *
	 * Covers real wp-cron (DOING_CRON) and admin "Run now" tools such as WP Crontrol,
	 * which fire hooks without defining DOING_CRON.
	 *
	 * Overridable in tests.
	 */
	protected function is_cron_request_context() {
		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return true;
		}

		// WP Crontrol / Tools → Cron Events "Run now": admin, not admin-ajax.
		return is_admin() && ! wp_doing_ajax();
	}

	/**
	 * Prepare data for process
	 */
	public function run() {
		// ? check whether process is age out and make it can work on Lasso UI via ajax requests
		$this->is_process_age_out();

		if ( $this->is_process_running() ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'Lasso Lite Amazon cron: run aborted; process already running' );
			return false;
		}

		$total_amz          = Amazon_Products::count_amazon_product_in_db();
		$limit              = max( 20, intval( ceil( $total_amz / ( 20 * 3 ) ) ) );
		$amazon_product_ids = Amazon_Products::get_amazon_product_in_db( $limit );
		$count              = count( $amazon_product_ids ) ?? 0;
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log(
			sprintf(
				'Lasso Lite Amazon cron: run start eligible=%d limit=%d queued=%d cron_ctx=%s',
				$total_amz,
				$limit,
				$count,
				$this->is_cron_request_context() ? '1' : '0'
			)
		);

		foreach ( $amazon_product_ids as $product ) {
			$id                   = Amazon_Api::get_product_id_by_url( $product['base_url'] );
			$product['amazon_id'] = '' === $product['amazon_id'] ? $id : $product['amazon_id'];

			$this->push_to_queue( $product['amazon_id'] );
		}

		$this->set_total( $count );
		$this->set_log_file_name( $this->log_name );
		$this->task_start_log();

		$this->save();

		// Cron / WP Crontrol "Run now": drain inline only — do not dispatch first.
		// A non-blocking dispatch can return success while a worker acquires the lock
		// before inline drain, so both would process the same batch.
		if ( $this->is_cron_request_context() ) {
			if ( ! $this->is_queue_empty() ) {
				if ( $this->is_process_running( true ) ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
					error_log(
						'Lasso Lite Amazon cron: skipping inline drain; background worker lock active'
					);
				} else {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
					error_log(
						'Lasso Lite Amazon cron: processing queue inline; remaining=' . $this->get_total_remaining()
					);
					$this->handle_for_cron();
				}
			}

			return true;
		}

		$dispatch_result = $this->dispatch();
		if ( is_wp_error( $dispatch_result ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log(
				'Lasso Lite Amazon cron: async dispatch failed: ' . $dispatch_result->get_error_message()
			);
		}

		return true;
	}

	/**
	 * Refresh Amazon products pricing
	 *
	 * @param string $product_id Amazon product id.
	 */
	private function update_amazon_pricing( $product_id ) {
		$lasso_amazon_api = new Amazon_Api();

		try {
			// ? if a product is checked very quick, we need to delay this in a short time
			// ? because amazon api will response an error when we request continuously
			sleep( 1 );

			$last_updated = gmdate( 'Y-m-d H:i:s', time() );
			$amazon_db    = $lasso_amazon_api->get_amazon_product_from_db( $product_id );
			$amz_link     = $amazon_db['monetized_url'] ?? '';
			$result       = $lasso_amazon_api->fetch_product_info( $product_id, true, $last_updated, $amz_link );
			$status       = (string) ( $result['status'] ?? '' );
			$error_code   = (string) ( $result['error_code'] ?? '' );

			if ( 'success' !== $status ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log(
					sprintf(
						'Lasso Lite Amazon cron: ASIN %s status=%s error_code=%s',
						$product_id,
						$status,
						$error_code
					)
				);
			}

			// NotFound: still bump last_updated so the next cron run skips this ASIN for 24h.
			if ( 'NotFound' === $error_code ) {
				$amz_model = new Amazon_Products();
				$amz_model->update_amazon_field( $product_id, 'last_updated', $last_updated );
			}
		} catch ( \Exception $e ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'Lasso Lite Amazon cron: ASIN ' . $product_id . ' exception: ' . $e->getMessage() );
		}
	}
}
new Amazon();
