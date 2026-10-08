<?php
/**
 * Lite Dashboard Intro.js product tour (#1134, parity with Pro #5894).
 * Completion meta is Lite-only (`lasso_lite_dashboard_tour_done`); do not share Pro's key.
 *
 * @package Classes
 */

namespace LassoLite\Classes;

/**
 * Class Product_Tour
 */
class Product_Tour {
	const SCRIPT_HANDLE_INTRO = 'lasso-introjs';
	const SCRIPT_HANDLE_TOUR  = 'lasso-product-tour';
	const STYLE_HANDLE_INTRO  = 'lasso-introjs';
	const STYLE_HANDLE_TOUR   = 'lasso-product-tour';

	/**
	 * Whether the current (or given) user has completed/skipped the tour.
	 *
	 * @param int $user_id User ID (defaults to current user).
	 * @return bool
	 */
	public static function is_done_for_user( $user_id = 0 ) {
		$user_id = $user_id > 0 ? (int) $user_id : get_current_user_id();
		if ( $user_id <= 0 ) {
			return false;
		}

		$value = get_user_meta( $user_id, Enum::USER_META_DASHBOARD_TOUR_DONE, true );

		return '' !== $value && '0' !== (string) $value;
	}

	/**
	 * Whether the tour should auto-start for this user.
	 *
	 * @param int $user_id User ID (defaults to current user).
	 * @return bool
	 */
	public static function should_show_for_user( $user_id = 0 ) {
		return ! self::is_done_for_user( $user_id );
	}

	/**
	 * Persist tour completion / skip for the user.
	 *
	 * @param int $user_id User ID (defaults to current user).
	 * @return void
	 */
	public static function mark_done_for_user( $user_id = 0 ) {
		$user_id = $user_id > 0 ? (int) $user_id : get_current_user_id();
		if ( $user_id <= 0 ) {
			return;
		}

		update_user_meta( $user_id, Enum::USER_META_DASHBOARD_TOUR_DONE, '1' );
	}

	/**
	 * True only on the Lite links Dashboard admin screen.
	 *
	 * @return bool
	 */
	public static function is_dashboard_links_page() {
		$setting = new Setting();

		return $setting->is_dashboard_page();
	}

	/**
	 * Enqueue Intro.js + tour bootstrap when on Dashboard and meta unset.
	 *
	 * @return bool True when assets were enqueued.
	 */
	public static function enqueue_assets() {
		if ( ! self::is_dashboard_links_page() ) {
			return false;
		}

		if ( ! self::should_show_for_user() ) {
			return false;
		}

		Helper::enqueue_style( self::STYLE_HANDLE_INTRO, 'introjs.min.css' );
		Helper::enqueue_style( self::STYLE_HANDLE_TOUR, 'lasso-product-tour.css', array( self::STYLE_HANDLE_INTRO ) );
		self::enqueue_purple_theme_inline();
		$intro_handle = SIMPLE_URLS_SLUG . '-' . self::SCRIPT_HANDLE_INTRO;
		$tour_handle  = SIMPLE_URLS_SLUG . '-' . self::SCRIPT_HANDLE_TOUR;

		Helper::enqueue_script( self::SCRIPT_HANDLE_INTRO, 'intro.min.js', array(), true );
		Helper::enqueue_script(
			self::SCRIPT_HANDLE_TOUR,
			'lasso-product-tour.js',
			array( 'jquery', $intro_handle ),
			true
		);

		wp_localize_script(
			$tour_handle,
			'lassoProductTour',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'lasso_ajax' ),
				'action'  => 'lasso_dismiss_dashboard_tour',
				'steps'   => self::get_steps(),
			)
		);

		return true;
	}

	/**
	 * Critical purple theme as inline CSS on the Intro.js handle (loads after vendor CSS).
	 *
	 * @return void
	 */
	public static function enqueue_purple_theme_inline() {
		$css = '
/* Beat lasso-dashboard.css `div{box-shadow:none!important}` so the spotlight dims the page. */
.introjs-helperLayer{box-shadow:0 0 1px 2px rgba(33,33,33,.8),rgba(33,33,33,.55) 0 0 0 5000px!important;opacity:1!important}
.introjs-light{--introjs-bg:#5e36ca!important;--introjs-color:#fff!important;--introjs-arrow-color:#5e36ca!important;--introjs-btn-bg:rgba(255,255,255,.14)!important;--introjs-btn-color:#fff!important;--introjs-btn-border:rgba(255,255,255,.45)!important;--introjs-btn-hover-bg:rgba(255,255,255,.24)!important;--introjs-btn-hover-color:#fff!important;--introjs-btn-focus-bg:rgba(255,255,255,.24)!important;--introjs-btn-focus-color:#fff!important;--introjs-btn-active-bg:rgba(255,255,255,.3)!important;--introjs-btn-active-color:#fff!important;--introjs-btn-active-border:rgba(255,255,255,.55)!important;--introjs-btn-textShadow:transparent!important}
.introjs-tooltipReferenceLayer .introjs-tooltip,.introjs-tooltip{background:#5e36ca!important;background-color:#5e36ca!important;color:#fff!important;border-radius:8px}
.introjs-tooltipReferenceLayer .introjs-tooltiptext,.introjs-tooltiptext,.introjs-tooltip-header,.introjs-tooltip-title{color:#fff!important}
.introjs-tooltipReferenceLayer .introjs-skipbutton,.introjs-skipbutton{top:8px!important;right:10px!important;width:auto!important;height:auto!important;line-height:1.2!important;padding:6px 10px!important;font-size:13px!important;font-weight:600!important;color:rgba(255,255,255,.9)!important}
.introjs-tooltipReferenceLayer .introjs-button,.introjs-tooltip .introjs-button{color:#fff!important;background-color:rgba(255,255,255,.14)!important;border-color:rgba(255,255,255,.45)!important;text-shadow:none!important}
.introjs-tooltipbuttons{border-top-color:rgba(255,255,255,.2)!important}
.introjs-bullets ul li a{background:rgba(255,255,255,.35)!important}
.introjs-bullets ul li a.active{background:#fff!important}
.introjs-arrow.top,.introjs-arrow.top-middle,.introjs-arrow.top-right{border-bottom-color:#5e36ca!important}
.introjs-arrow.bottom,.introjs-arrow.bottom-middle,.introjs-arrow.bottom-right{border-top-color:#5e36ca!important}
.introjs-arrow.left,.introjs-arrow.left-bottom{border-right-color:#5e36ca!important}
.introjs-arrow.right,.introjs-arrow.right-bottom{border-left-color:#5e36ca!important}
';
		wp_add_inline_style( SIMPLE_URLS_SLUG . '-' . self::STYLE_HANDLE_INTRO, $css );
	}

	/**
	 * Tour step definitions (selectors match Lite Dashboard chrome).
	 *
	 * @return array<int, array<string, string>>
	 */
	public static function get_steps() {
		return array(
			array(
				'element'  => '#lasso-header-add',
				'intro'    => __( 'Create your first Lasso link here. Paste any URL to start customizing and sharing it.', 'simple-urls' ),
				'position' => 'bottom',
			),
			array(
				'element'  => '#lasso-header-nav a[href*="surl-dashboard"]',
				'intro'    => __( 'Your Dashboard lists every Lasso link. Filter, search, and open any link to edit it.', 'simple-urls' ),
				'position' => 'bottom',
			),
			array(
				'element'  => '#link-search-input',
				'intro'    => __( 'Search by link name or URL. Use the keyboard shortcut shown next to the box for a faster jump.', 'simple-urls' ),
				'position' => 'bottom',
			),
			array(
				'element'  => '#lasso-header-nav a[href*="opportunities"]',
				'intro'    => __( 'Opportunities identify links that aren\'t monetized yet or could be earning higher commissions.', 'simple-urls' ),
				'position' => 'bottom',
			),
			array(
				'element'  => '#lasso-header-nav a[href*="surl-groups"]',
				'intro'    => __( 'Create Groups to organize related links so you can manage and display them together.', 'simple-urls' ),
				'position' => 'bottom',
			),
			array(
				'element'  => '#lasso-header-nav a[href*="surl-tables"]',
				'intro'    => __( 'Tables are comparison layouts you can embed on posts. Build once, reuse across your site.', 'simple-urls' ),
				'position' => 'bottom',
			),
			array(
				'element'   => '#lasso-tour-chat-anchor',
				'title'     => __( 'Chat', 'simple-urls' ),
				'intro'     => __( 'Need additional help? Chat with the Lasso team anytime by clicking this button.', 'simple-urls' ),
				'position'  => 'top',
				'lassoChat' => true,
			),
		);
	}
}
