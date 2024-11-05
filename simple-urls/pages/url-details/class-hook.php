<?php
/**
 * Lasso Lite Url detail - Hook.
 *
 * @package Pages
 */

namespace LassoLite\Pages\Url_Details;

use LassoLite\Classes\Affiliate_Link;
use LassoLite\Classes\Amazon_Api;
use LassoLite\Admin\Constant;
use LassoLite\Classes\Enum;
use LassoLite\Classes\Helper;
use LassoLite\Classes\License;
use LassoLite\Classes\Setting;

/**
 * Lasso Lite Url detail - Hook.
 */
class Hook {
	/**
	 * Declare "Lasso Lite register hook events" to WordPress.
	 */
	public function register_hooks() {
		// ? change Edit URL in Dashboard
		add_filter( 'get_edit_post_link', array( $this, 'affiliate_link_edit_post_link' ), 10, 3 );
		add_action( 'wp_ajax_lasso_lite_upload_thumbnail', array( $this, 'upload_thumbnail' ) );
	}

	/**
	 * Change edit post link for Lasso Lite post
	 *
	 * @param string $url     The edit link.
	 * @param int    $post_id Post ID.
	 * @param string $context The link context. If set to 'display' then ampersands are encoded.
	 */
	public function affiliate_link_edit_post_link( $url, $post_id, $context ) {

		$post_type = get_post_type( $post_id );

		if ( SIMPLE_URLS_SLUG === $post_type && ( get_option( Enum::LASSO_LITE_ACTIVE ) || Helper::is_lite_using_new_ui() ) ) {
			$url = Affiliate_Link::affiliate_edit_link( $post_id );
		}

		return $url;
	}

	/**
	 * Upload thumbnail
	 *
	 * @param string $image_url Image url.
	 */
	public function upload_thumbnail( $image_url = '' ) {
		$data           = wp_unslash( $_POST ); // phpcs:ignore
		$lasso_id       = intval( $data['lasso_id'] ?? 0 );
		$product_url    = $data['product_url'] ?? '';
		$image_url      = $data['image_url'] ?? $image_url;
		$product_name   = '';
		$is_product_url = $data['is_product_url'] ?? false;
		$amazon_product = false;

		$is_amazon_configured = Amazon_Api::is_amazon_setting_configured();
		$license_status       = License::get_license_status();
		if ( ! $is_amazon_configured || ! $license_status ) {
			$this->lasso_ajax_error( 'This feature is disabled due to Amazon setting issue.' );
		}

		$product_id = Amazon_Api::get_product_id_by_url( $product_url );

		if ( 0 === $lasso_id ) {
			$this->lasso_ajax_error( 'Lasso ID (' . $lasso_id . ') is invalid.' );
		}

		if ( '' === $product_id ) {
			$this->lasso_ajax_error( 'Product ID (' . $product_id . ') is invalid.' );
		}

		// ? send request to broken link service
		$lasso_amazon_api = new Amazon_Api();
		if ( $is_product_url ) {
			$amazon_product = $lasso_amazon_api->fetch_product_info( $product_id, true, false, $product_url );
			$amazon_product = $amazon_product['product'];
			if ( isset( $amazon_product['status_code'] ) && 200 === $amazon_product['status_code'] ) {
				$image_url    = $amazon_product['image'] ?? $image_url;
				$product_name = $amazon_product['title'] ?? '';
			} else {
				$this->lasso_ajax_error( 'Fetch status was not 200.' );
			}
		} else {
			$this->lasso_ajax_error( "Don't run BLS, not an Amazon Product." );
		}

		// We have an Amazon Image, let's hook it up.
		if ( ! empty( $image_url ) && Constant::DEFAULT_THUMBNAIL !== $image_url ) {
			delete_post_thumbnail( $lasso_id );
			update_post_meta( $lasso_id, 'lasso_custom_thumbnail', $image_url );
		}

		// ? Set Amazon additional data
		if ( ! empty( $amazon_product ) && isset( $amazon_product['price'] ) && isset( $amazon_product['savings_basis'] ) && isset( $amazon_product['currency'] ) ) {
			$amazon_product['show_discount_pricing'] = Setting::get_setting( 'show_amazon_discount_pricing' );
			$amazon_product['discount_pricing_html'] = Amazon_Api::build_discount_pricing_html( $amazon_product['price'], $amazon_product['savings_basis'], $amazon_product['currency'] );
		}

		if ( isset( $image_url ) ) {
			wp_send_json_success(
				array(
					'status'         => 1,
					'amazon_product' => $amazon_product,
					'thumbnail'      => $image_url,
					'thumbnail_id'   => 0,
					'product_name'   => $product_name,
				)
			);
		} else {
			$this->lasso_ajax_error( "For some reason the image_url isn't set, weird issue." );
		}
	} // @codeCoverageIgnore

	/**
	 * Send error via ajax request
	 *
	 * @param string $error_message Error message.
	 */
	private function lasso_ajax_error( $error_message ) {
		wp_send_json_success(
			array(
				'status' => 0,
				'error'  => $error_message,
			)
		);
	} // @codeCoverageIgnore
}
