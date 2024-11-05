<?php
/**
 * Declare class Post
 *
 * @package Post
 */

namespace LassoLite\classes;

use LassoLite\Classes\Affiliate_Link as Lasso_Affiliate_Link;
use LassoLite\Classes\Elementor as Lasso_Elementor;

/**
 * Handler wp_posts
 * Post
 */
class Post {

	/**
	 * WP_Post
	 *
	 * @var array|WP_Post|null
	 */
	private $post;

	/**
	 * Lasso ID
	 *
	 * @var int
	 */
	private $lasso_id;

	/**
	 * Lasso URL
	 *
	 * @var object
	 */
	private $lasso_url;

	/**
	 * Post constructor.
	 *
	 * @param int    $post_id   WP_Post ID.
	 * @param int    $lasso_id  Lasso ID.
	 * @param object $lasso_url Lasso url.
	 */
	public function __construct( $post_id, $lasso_id, $lasso_url = null ) {
		$this->post      = get_post( $post_id );
		$this->lasso_id  = $lasso_id;
		$this->lasso_url = $lasso_url ? $lasso_url : Lasso_Affiliate_Link::get_lasso_url( $post_id );
	}

	/**
	 * Init instance
	 *
	 * @param int    $post_id   WP_Post ID.
	 * @param object $lasso_url Lasso url.
	 *
	 * @return Post
	 */

	/**
	 * Get wp post ID
	 *
	 * @return int
	 */
	public function get_post_id() {
		return $this->post->ID;
	}

	/**
	 * Get JSON meta
	 *
	 * @param string $key Meta key.
	 *
	 * @return array
	 */
	public function get_json_meta( $key ) {
		$meta = get_post_meta( $this->post->ID, $key, true );

		if ( is_string( $meta ) && ! empty( $meta ) ) {
			$meta = json_decode( $meta, true );
		}

		if ( empty( $meta ) ) {
			$meta = array();
		}

		return $meta;
	}
}
