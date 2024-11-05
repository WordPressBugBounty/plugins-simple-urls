<?php
/**
 * Declare class Elementor
 *
 * @package Elementor
 */

namespace LassoLite\Classes;

use LassoLite\Classes\Post as Lasso_Post;
use LassoLite\Classes\Helper as Lasso_Helper;

use LassoLite\Classes\Cron as Lasso_Cron;
use simple_html_dom;

/**
 *
 * Elementor
 */
class Elementor {
	/**
	 * Lasso Post
	 *
	 * @var Lasso_Post Lasso_Post Lasso Post class.
	 */
	private $lasso_post;

	/**
	 * Link location ID
	 *
	 * @var int $link_location_id Link location ID.
	 */
	private $link_location_id;

	/**
	 *  Link to redirect
	 *
	 * @var string $link_redirect Link to redirect.
	 */
	private $link_redirect;

	/**
	 * Mode monetize or save
	 *
	 * @var string $mode Mode monetize etc...
	 */
	private $mode;

	/**
	 * Elementor plugin key to get data
	 *
	 * @var string $elementor_data_key Elementor plugin key to get data.
	 */
	private $elementor_data_key = '_elementor_data';

	/**
	 * Elementor constructor.
	 *
	 * @param int    $post_id  Post ID.
	 * @param int    $lasso_id Lasso ID.
	 * @param string $mode     Mode.
	 */
	public function __construct( $post_id, $lasso_id, $mode ) {
		$this->lasso_post = new Lasso_Post( $post_id, $lasso_id );
		$this->mode       = $mode;
	}

	/**
	 * Override post meta data of Elementor
	 */
	public function update_content() {
		$editor_data = $this->get_data();

		// ? We need the `wp_slash` in order to avoid the unslashing during the `update_post_meta`
		$json_value = wp_slash( wp_json_encode( $editor_data ) );
		update_metadata( 'post', $this->lasso_post->get_post_id(), $this->elementor_data_key, $json_value );
	}

	/**
	 * Get data
	 *
	 * @return array
	 */
	public function get_data() {
		return $this->lasso_post->get_json_meta( $this->elementor_data_key );
	}
}
