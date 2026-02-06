<?php
/**
 * Declare class Config
 *
 * @package Config
 */

namespace LassoLite\Classes;

/**
 * Config
 */
class Config {
	/**
	 * Track whether we opened the layout wrapper in get_header().
	 *
	 * @var bool
	 */
	private static $layout_wrapper_open = false;

	/**
	 * Print header html
	 */
	public static function get_header() {
		self::$layout_wrapper_open = true;
		echo '<div class="lasso-lite-layout">';
		require_once SIMPLE_URLS_DIR . '/admin/views/header.php';
	}

	/**
	 * Print footer html
	 */
	public static function get_footer() {
		require_once SIMPLE_URLS_DIR . '/admin/views/footer.php';
		if ( self::$layout_wrapper_open ) {
			echo '</div>';
			self::$layout_wrapper_open = false;
		}
	}
}
