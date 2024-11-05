<?php
/**
 * Declare class Lasso_Process_Update_Amazon
 *
 * @package Lasso_Process_Update_Amazon
 */

namespace LassoLite\Classes\Processes;

use LassoLite\Admin\Constant;
use LassoLite\Classes\Affiliate_Link;
use LassoLite\Classes\Amazon_Api;
use LassoLite\Classes\Helper;
use LassoLite\Classes\Lasso_DB;
use LassoLite\Classes\Meta_Enum;
use LassoLite\Classes\Processes\Process;

use LassoLite\Models\Url_Details;

/**
 * Lasso_Process_Update_Amazon
 */
class Amazon_Shortlink extends Process {
	/**
	 * Action name
	 *
	 * @var string $action
	 */
	protected $action = 'lassolite_amazon_shortlink_process';

	/**
	 * Log name
	 *
	 * @var string $log_name
	 */
	protected $log_name = 'amazon_shortlink';

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $lasso_id Queue item to iterate over.
	 *
	 * @return mixed
	 */
	public function task( $lasso_id ) {
		if ( empty( $lasso_id ) ) {
			return false;
		}

		$model = new Url_Details();
		$url_details = $model->get_one( $lasso_id );
		if ( ! $url_details ) {
			return false;
		}

		$this->update_post_modified_gmt( $lasso_id ); // ? Update post modified time

		$shortlink = $url_details->get_redirect_url();
		// $shortlink_meta = get_post_meta( $lasso_id, Meta_Enum::SURL_REDIRECT, true );
		// if ( 'amzn.to' !== Helper::get_base_domain( $shortlink_meta ) && 'amzn.to' !== Helper::get_base_domain( $shortlink ) ) {
		// 	return false;
		// }

		$is_opportunity = 1;
		$get_final_url  = Helper::get_redirect_final_target( $shortlink );
		$base_domain    = Helper::get_base_domain( $get_final_url );
		$product_id     = Amazon_Api::get_product_id_by_url( $get_final_url );
		$tracking_id    = Amazon_Api::get_amazon_tracking_id_by_url( $get_final_url );
		$product_type   = Amazon_Api::is_amazon_url( $get_final_url ) ? Amazon_Api::PRODUCT_TYPE : '';
		$get_final_url  = Amazon_Api::get_amazon_product_url( $get_final_url, true, false, $tracking_id );
		if ( $product_id ) {
			$lasso_db         = new Lasso_DB();
			$lasso_db->update_url_details( $lasso_id, $get_final_url, $base_domain, $is_opportunity, $product_id, $product_type );

			$lasso_amazon_api = new Amazon_Api();
			$amazon_db = $lasso_amazon_api->get_amazon_product_from_db( $product_id );
			if ( ! $amazon_db ) {
				$this->update_amazon_pricing( $product_id );
			}
			update_post_meta( $lasso_id, Meta_Enum::SURL_REDIRECT, $get_final_url );
			
			$amazon_db = $lasso_amazon_api->get_amazon_product_from_db( $product_id );
			if ( $amazon_db ) {
				$product_name = $amazon_db['default_product_name'] ?? '';
				$product_image = $amazon_db['default_image'] ?? '';
				$product_price = $amazon_db['latest_price'] ?? '';

				if ( $product_price ) {
					update_post_meta( $lasso_id, Meta_Enum::PRICE, $product_price );
				}

				if ( $product_name && Affiliate_Link::DEFAULT_AMAZON_NAME == get_the_title( $lasso_id ) ) {
					$post_data = array(
						'ID' => $lasso_id,
						'post_title' => $product_name,
					);
					
					wp_update_post($post_data);
				}

				$db_thumbnail = get_post_meta( $lasso_id, Meta_Enum::LASSO_LITE_CUSTOM_THUMBNAIL, true );
				if ( $product_image && $db_thumbnail === Constant::DEFAULT_THUMBNAIL ) {
					update_post_meta( $lasso_id, Meta_Enum::LASSO_LITE_CUSTOM_THUMBNAIL, $product_image );
				}
			}
		}

		// $this->update_amazon_pricing( $product_id );
		$this->set_processing_runtime();

		return false;
	}

	/**
	 * Prepare data for process
	 */
	public function run() {
		// ? check whether process is age out and make it can work on Lasso UI via ajax requests
		$this->is_process_age_out();

		if ( $this->is_process_running() ) {
			return false;
		}

		$limit              = 10;
		$lasso_ids = Url_Details::get_amzn_to_shortlinks( $limit );
		$count              = count( $lasso_ids ) ?? 0;

		foreach ( $lasso_ids as $lasso ) {
			$this->push_to_queue( $lasso->lasso_id );
		}

		$this->set_total( $count );
		$this->set_log_file_name( $this->log_name );
		$this->task_start_log();

		$this->save()->dispatch();

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
			$lasso_amazon_api->fetch_product_info( $product_id, true, $last_updated, $amz_link );
		} catch ( \Exception $e ) {
			// ? error
		}
	}

	private function update_post_modified_gmt( $lasso_id ) {
		$time = current_time('mysql', 1); // Get current time in MySQL format, adjusted for GMT

		$post_data = array(
			'ID' => $lasso_id,
			'post_modified' => $time,
			'post_modified_gmt' => get_gmt_from_date($time)
		);

		wp_update_post( $post_data );
	}
}
new Amazon_Shortlink();
