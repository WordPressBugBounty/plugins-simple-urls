<?php
/**
 * Lasso Lite General - Ajax.
 *
 * @package Pages
 */

namespace LassoLite\Pages;

use LassoLite\Admin\Constant;

use LassoLite\Classes\Affiliate_Link;
use LassoLite\Classes\Enum;
use LassoLite\Classes\Meta_Enum;
use LassoLite\Classes\Helper;
use LassoLite\Classes\Setting;
use LassoLite\Classes\SURL;

/**
 * Lasso General - Ajax.
 */
class Ajax {
	/**
	 * Declare "Lasso Lite ajax requests" to WordPress.
	 */
	public function register_hooks() {
		add_action( 'wp_ajax_lasso_lite_add_a_new_link', array( $this, 'lasso_lite_add_a_new_link' ) );
		add_action( 'wp_ajax_lasso_lite_get_single', array( $this, 'lasso_lite_get_single' ) );
		add_action( 'wp_ajax_lasso_lite_get_shortcode_content', array( $this, 'lasso_lite_get_shortcode_content' ) );
		add_action( 'wp_ajax_lasso_lite_get_display_html', array( $this, 'lasso_lite_get_display_html' ) );
		add_action( 'wp_ajax_lasso_lite_get_link_quick_detail', array( $this, 'lasso_lite_get_link_quick_detail' ) );
		add_action( 'wp_ajax_lasso_lite_save_link_quick_detail', array( $this, 'lasso_lite_save_link_quick_detail' ) );
		add_action( 'wp_ajax_lasso_lite_get_click_snapshot', array( $this, 'lasso_lite_get_click_snapshot' ) );
		add_action( 'wp_ajax_lasso_lite_get_link_issues_snapshot', array( $this, 'lasso_lite_get_link_issues_snapshot' ) );
		add_action( 'wp_ajax_lasso_lite_get_links_issues_totals', array( $this, 'lasso_lite_get_links_issues_totals' ) );
		add_action( 'wp_ajax_lasso_lite_get_earnings_estimate', array( $this, 'lasso_lite_get_earnings_estimate' ) );
		add_action( 'wp_ajax_lasso_lite_get_external_signup_config', array( $this, 'lasso_lite_get_external_signup_config' ) );
		add_action( 'wp_ajax_lasso_lite_external_signup', array( $this, 'lasso_lite_external_signup' ) );
		add_action( 'wp_ajax_lasso_lite_external_signup_exchange', array( $this, 'lasso_lite_external_signup_exchange' ) );
		add_action( 'wp_ajax_lasso_lite_get_setup_progress', array( $this, 'lasso_lite_get_setup_progress' ) );
		add_action( 'wp_ajax_lasso_lite_save_support', array( $this, 'lasso_lite_save_support' ) );
		add_action( 'wp_ajax_lasso_lite_save_lasso_account', array( $this, 'lasso_lite_save_lasso_account' ) );
		add_action( 'wp_ajax_lasso_lite_check_existing_account', array( $this, 'lasso_lite_check_existing_account' ) );
		add_action( 'wp_ajax_lasso_lite_review_snooze', array( $this, 'lasso_lite_review_snooze' ) );
		add_action( 'wp_ajax_lasso_lite_disable_review', array( $this, 'lasso_lite_disable_review' ) );
		add_action( 'wp_ajax_lasso_lite_dismiss_notice', array( $this, 'lasso_lite_dismiss_notice' ) );
		add_action( 'wp_ajax_lasso_lite_disable_affiliate_promotions', array( $this, 'lasso_lite_disable_affiliate_promotions' ) );
	}

	/**
	 * Add a new Lasso link
	 */
	public function lasso_lite_add_a_new_link() {
		Helper::verify_access_and_nonce( true );

		$lasso_lite_affiliate_link = new Affiliate_Link();
		return $lasso_lite_affiliate_link->add_a_new_link();
	}

	/**
	 * Get display html
	 */
	public function lasso_lite_get_single() {
		Helper::verify_access_and_nonce( true );

		// phpcs:ignore
		$post    = Helper::POST();
		$page    = intval( $post['page'] ) ?? 1;
		$limit   = intval( $post['limit'] ) ?? 5;
		$keyword = $post['keyword'] ?? '';
		$list    = SURL::get_list( $keyword, $page, $limit );
		$output  = array();

		foreach ( $list as $surl ) {
			$lasso_url = Affiliate_Link::get_lasso_url( $surl->get_id() );

			$output[] = array(
				'post_id'     => $lasso_url->id,
				'title'       => $lasso_url->name,
				'permalink'   => $lasso_url->permalink,
				'link_detail' => $lasso_url->edit_link,
				'img_src'     => $lasso_url->image_src,
				'redirect'    => $lasso_url->target_url,
				'slug'        => $lasso_url->slug,
			);
		}

		$data['output']        = $output;
		$data['total']         = SURL::total( $keyword );
		$data['limit_on_page'] = $limit;
		$data['page']          = $page;

		wp_send_json_success( $data );
	}

	/**
	 * Get display html
	 */
	public function lasso_lite_get_shortcode_content() {
		Helper::verify_access_and_nonce( true );

		$shortcode = stripslashes( Helper::GET()['shortcode'] ?? '' ); // phpcs:ignore
		$html      = '';

		if ( '' !== $shortcode ) {
			$html = do_shortcode( $shortcode );
		}

		wp_send_json_success(
			array(
				'shortcode' => $shortcode,
				'html'      => $html,
			)
		);
	}

	/**
	 * Get display html
	 */
	public function lasso_lite_get_display_html() {
		Helper::verify_access_and_nonce( true );
		$html = Helper::get_display_modal_html();

		wp_send_json_success(
			array(
				'html' => $html,
			)
		);
	}

	/**
	 * Get a new Lasso quick link
	 */
	public function lasso_lite_get_link_quick_detail() {
		Helper::verify_access_and_nonce( true );

		$lasso_id = Helper::POST()['post_id'] ?? null; // phpcs:ignore

		if ( ! empty( $lasso_id ) ) {
			$lasso_url = Affiliate_Link::get_lasso_url( $lasso_id, true );

			wp_send_json_success(
				array(
					'success'   => true,
					'lasso_url' => $lasso_url,
				)
			);
		} else {
			wp_send_json_error( 'No affiliate link to get.' );
		}
	}

	/**
	 * Save a lasso link with basic data
	 */
	public function lasso_lite_save_link_quick_detail() {
		Helper::verify_access_and_nonce( true );

		$data         = Helper::POST(); // phpcs:ignore
		$lasso_id     = $data['lasso_id'] ?? null; // phpcs:ignore
		$lasso_post   = get_post( $lasso_id );
		$thumbnail_id = intval( $data['thumbnail_id'] ?? 0 );

		if ( $lasso_post ) {
			$lasso_lite_post = array(
				'ID'         => $lasso_post->ID,
				'post_title' => trim( $data['affiliate_name'] ),
				'meta_input' => array(
					Meta_Enum::LASSO_LITE_CUSTOM_THUMBNAIL => trim( $data['thumbnail_image_url'] ?? '' ),
					Meta_Enum::BUY_BTN_TEXT                => trim( $data['buy_btn_text'] ?? '' ),
					Meta_Enum::DESCRIPTION                 => trim( $data['description'] ?? '' ),
					Meta_Enum::BADGE_TEXT                  => trim( $data['badge_text'] ?? '' ),
				),
			);

			wp_update_post( $lasso_lite_post );

			// ? update thumbnail
			if ( $thumbnail_id > 0 ) {
				set_post_thumbnail( $lasso_id, $thumbnail_id );
				$image_url = wp_get_attachment_url( $thumbnail_id );
				update_post_meta( $lasso_id, Meta_Enum::LASSO_LITE_CUSTOM_THUMBNAIL, $image_url );
			} else {
				delete_post_thumbnail( $lasso_id );
			}

			clean_post_cache( $lasso_id ); // ? clean post cache
			wp_send_json_success(
				array(
					'success' => true,
				)
			);
		} else {
			wp_send_json_success(
				array(
					'success' => false,
					'msg'     => 'No affiliate link existed.',
				)
			);
		}
	}

	/**
	 * Get click snapshot data
	 */
	public function lasso_lite_get_click_snapshot() {
		Helper::verify_access_and_nonce();

		$post      = Helper::POST(); // phpcs:ignore
		$use_cache = '0' !== (string) ( $post['use_cache'] ?? '1' ); // phpcs:ignore

		$transient_key = 'lasso_lite_click_snapshot_' . md5( \site_url() );
		$cache_ttl     = (int) \apply_filters( 'lasso_lite_click_snapshot_cache_ttl', 6 * \HOUR_IN_SECONDS );

		if ( $use_cache ) {
			$cached = \get_transient( $transient_key );
			if ( false !== $cached ) {
				wp_send_json_success( $cached );
			}
		}

		$response = Helper::send_request(
			'get',
			Constant::LASSO_LINK . '/clicks/lasso-lite/monthly',
			array(),
			array(
				'site-url' => \site_url(),
			)
		);

		if ( empty( $response['response'] ) || $response['status_code'] >= 400 ) {
			wp_send_json_error(
				array(
					'msg'         => 'Unable to fetch click snapshot.',
					'status_code' => $response['status_code'] ?? null,
					'response'    => $response['response'] ?? null,
				)
			);
		}

		if ( $use_cache ) {
			\set_transient( $transient_key, $response['response'], $cache_ttl );
		}

		wp_send_json_success( $response['response'] );
	}

	/**
	 * Get link issues snapshot data
	 */
	public function lasso_lite_get_link_issues_snapshot() {
		Helper::verify_access_and_nonce();

		$post      = Helper::POST(); // phpcs:ignore
		$use_cache = '0' !== (string) ( $post['use_cache'] ?? '1' ); // phpcs:ignore

		$transient_key = 'lasso_lite_link_issues_snapshot_' . md5( \site_url() );
		$cache_ttl     = (int) \apply_filters( 'lasso_lite_link_issues_snapshot_cache_ttl', 6 * \HOUR_IN_SECONDS );

		if ( $use_cache ) {
			$cached = \get_transient( $transient_key );
			if ( false !== $cached ) {
				wp_send_json_success( $cached );
			}
		}

		$response = Helper::send_request(
			'get',
			Constant::LASSO_LINK . '/lite/notify-snappshot',
			array(),
			array(
				'site-url' => \site_url(),
			)
		);

		if ( empty( $response['response'] ) || $response['status_code'] >= 400 ) {
			wp_send_json_error(
				array(
					'msg'         => 'Unable to fetch link issues snapshot.',
					'status_code' => $response['status_code'] ?? null,
					'response'    => $response['response'] ?? null,
				)
			);
		}

		if ( $use_cache ) {
			\set_transient( $transient_key, $response['response'], $cache_ttl );
		}

		wp_send_json_success( $response['response'] );
	}

	/**
	 * Get link issues totals data (broken/out-of-stock/opportunities) for dashboard pills.
	 */
	public function lasso_lite_get_links_issues_totals() {
		Helper::verify_access_and_nonce();

		$post      = Helper::POST(); // phpcs:ignore
		$use_cache = '0' !== (string) ( $post['use_cache'] ?? '1' ); // phpcs:ignore

		$transient_key = 'lasso_lite_links_issues_totals_' . md5( \site_url() );
		$cache_ttl     = (int) \apply_filters( 'lasso_lite_links_issues_totals_cache_ttl', 6 * \HOUR_IN_SECONDS );

		if ( $use_cache ) {
			$cached = \get_transient( $transient_key );
			if ( false !== $cached ) {
				wp_send_json_success( $cached );
			}
		}

		$response = Helper::send_request(
			'get',
			Constant::LASSO_LINK . '/api/links/issues',
			array(),
			array(
				'site-url' => \site_url(),
			)
		);

		if ( empty( $response['response'] ) || $response['status_code'] >= 400 ) {
			wp_send_json_error(
				array(
					'msg'         => 'Unable to fetch link issues totals.',
					'status_code' => $response['status_code'] ?? null,
					'response'    => $response['response'] ?? null,
				)
			);
		}

		if ( $use_cache ) {
			\set_transient( $transient_key, $response['response'], $cache_ttl );
		}

		wp_send_json_success( $response['response'] );
	}

	/**
	 * Get estimated earnings for this site (used for monthly notification).
	 */
	public function lasso_lite_get_earnings_estimate() {
		Helper::verify_access_and_nonce();

		$post      = Helper::POST(); // phpcs:ignore
		$use_cache = '0' !== (string) ( $post['use_cache'] ?? '1' ); // phpcs:ignore

		$transient_key = 'lasso_lite_earnings_estimate_' . md5( \site_url() );
		$cache_ttl     = (int) \apply_filters( 'lasso_lite_earnings_estimate_cache_ttl', 6 * \HOUR_IN_SECONDS );

		if ( $use_cache ) {
			$cached = \get_transient( $transient_key );
			if ( false !== $cached ) {
				wp_send_json_success( $cached );
			}
		}

		$response = Helper::send_request(
			'get',
			Constant::LASSO_LINK . '/api/earnings/estimate',
			array(),
			array(
				'site-url' => \site_url(),
			)
		);

		if ( empty( $response['response'] ) || $response['status_code'] >= 400 ) {
			wp_send_json_error(
				array(
					'msg'         => 'Unable to fetch earnings estimate.',
					'status_code' => $response['status_code'] ?? null,
					'response'    => $response['response'] ?? null,
				)
			);
		}

		if ( $use_cache ) {
			\set_transient( $transient_key, $response['response'], $cache_ttl );
		}

		wp_send_json_success( $response['response'] );
	}

	/**
	 * Get external signup config
	 */
	public function lasso_lite_get_external_signup_config() {
		Helper::verify_access_and_nonce();

		$post         = Helper::POST(); // phpcs:ignore
		$callback_url = esc_url_raw( $post['callback_url'] ?? '' );
		$source       = sanitize_text_field( $post['source'] ?? 'lasso-lite' );

		if ( empty( $callback_url ) ) {
			wp_send_json_error(
				array(
					'msg' => 'Missing callback URL.',
				)
			);
		}

		$request_url = add_query_arg(
			array(
				'source'       => $source,
				'callback_url' => $callback_url,
			),
			Constant::LASSO_HUB_URL . '/api/account/external/config'
		);

		$response = Helper::send_request( 'get', $request_url );

		if ( empty( $response['response'] ) || $response['status_code'] >= 400 ) {
			wp_send_json_error(
				array(
					'msg' => 'Unable to fetch signup config.',
				)
			);
		}

		wp_send_json_success( $response['response'] );
	}

	/**
	 * External signup
	 */
	public function lasso_lite_external_signup() {
		Helper::verify_access_and_nonce();

		$post     = Helper::POST(); // phpcs:ignore
		$email    = sanitize_email( $post['email'] ?? '' );
		$password = (string) ( $post['password'] ?? '' );
		$source   = sanitize_text_field( $post['source'] ?? 'lasso-lite' );

		if ( empty( $email ) || empty( $password ) ) {
			wp_send_json_error(
				array(
					'msg' => 'Missing required fields.',
				)
			);
		}

		$data     = array(
			'email'    => $email,
			'password' => $password,
			'source'   => $source,
		);
		$headers  = array(
			'Content-Type' => 'application/json',
		);
		$response = Helper::send_request( 'post', Constant::LASSO_HUB_URL . '/api/signup/external', $data, $headers );

		if ( empty( $response['response'] ) || $response['status_code'] >= 400 ) {
			$error_message = 'Signup failed.';
			if ( ! empty( $response['response'] ) ) {
				$error_message = $response['response']->error ?? $response['response']->message ?? $error_message;
			}

			wp_send_json_error(
				array(
					'msg'         => $error_message,
					'status_code' => $response['status_code'],
					'response'    => $response['response'],
				)
			);
		}

		wp_send_json_success( $response['response'] );
	}

	/**
	 * External signup exchange
	 */
	public function lasso_lite_external_signup_exchange() {
		Helper::verify_access_and_nonce();

		$post          = Helper::POST(); // phpcs:ignore
		$exchange_code = sanitize_text_field( $post['exchange_code'] ?? '' );

		if ( empty( $exchange_code ) ) {
			wp_send_json_error(
				array(
					'msg' => 'Missing exchange code.',
				)
			);
		}

		$data     = array(
			'exchange_code' => $exchange_code,
		);
		$headers  = array(
			'Content-Type' => 'application/json',
		);
		$response = Helper::send_request( 'post', Constant::LASSO_HUB_URL . '/api/signup/external/exchange', $data, $headers );

		if ( empty( $response['response'] ) || $response['status_code'] >= 400 ) {
			wp_send_json_error(
				array(
					'msg' => 'Exchange failed.',
				)
			);
		}

		wp_send_json_success( $response['response'] );
	}

	/**
	 * Get setup progress information
	 */
	public function lasso_lite_get_setup_progress() {
		wp_send_json_success( Helper::get_setup_progress_information() );
	}

	/**
	 * Do save support to open intercom chat
	 */
	public function lasso_lite_save_support() {
		Helper::verify_access_and_nonce();

		Setting::save_support();
	}

	/**
	 * Save Lasso account credentials after signup
	 */
	public function lasso_lite_save_lasso_account() {
		Helper::verify_access_and_nonce();

		$post    = Helper::POST();
		$email   = sanitize_email( $post['email'] ?? '' );
		$api_key = sanitize_text_field( $post['api_key'] ?? '' );
		$user_id = intval( $post['user_id'] ?? 0 );

		if ( empty( $email ) || empty( $api_key ) ) {
			wp_send_json_error( array( 'msg' => 'Missing required fields' ) );
			return;
		}

		Helper::update_option( Constant::LASSO_ACCOUNT_EMAIL, $email );
		Helper::update_option( Constant::LASSO_ACCOUNT_API_KEY, $api_key );
		Helper::update_option( Constant::LASSO_ACCOUNT_USER_ID, $user_id );
		Setting::set_setting( Enum::EMAIL_SUPPORT, $email );

		wp_send_json_success(
			array(
				'success' => true,
				'msg'     => 'Account saved successfully',
			)
		);
	}

	/**
	 * Check whether current site already has an account in Lasso (via lasso.link),
	 * and if yes sync WP options so the footer signup CTA won't show.
	 */
	public function lasso_lite_check_existing_account() {
		Helper::verify_access_and_nonce();

		$is_connected_aff      = intval( Helper::get_option( Constant::LASSO_OPTION_IS_CONNECTED_AFFILIATE, '0' ) );
		$lasso_account_email   = Helper::get_option( Constant::LASSO_ACCOUNT_EMAIL, '' );
		$lasso_account_user_id = intval( Helper::get_option( Constant::LASSO_ACCOUNT_USER_ID, 0 ) );
		$is_lasso_connected    = ! empty( $lasso_account_email ) || $lasso_account_user_id > 0 || $is_connected_aff > 0;

		if ( $is_lasso_connected ) {
			wp_send_json_success(
				array(
					'connected' => true,
					'synced'    => false,
				)
			);
		}

		$lookup = Helper::send_request(
			'get',
			rtrim( Constant::LASSO_LINK, '/' ) . '/account/existing',
			array(),
			array(
				'site-url' => \site_url(),
			)
		);

		$lookup_body = $lookup['response'] ?? null;
		if ( empty( $lookup_body ) || ( $lookup['status_code'] ?? 500 ) >= 400 || empty( $lookup_body->data ) ) {
			wp_send_json_success(
				array(
					'connected' => false,
					'synced'    => false,
				)
			);
		}

		if ( empty( $lookup_body->data->exists ) ) {
			wp_send_json_success(
				array(
					'connected' => false,
					'synced'    => false,
					'exists'    => false,
				)
			);
		}

		$email   = sanitize_email( $lookup_body->data->email ?? '' );
		$user_id = intval( $lookup_body->data->user_id ?? 0 );
		if ( empty( $email ) || $user_id <= 0 ) {
			wp_send_json_success(
				array(
					'connected' => false,
					'synced'    => false,
					'exists'    => true,
				)
			);
		}

		Helper::update_option( Constant::LASSO_ACCOUNT_EMAIL, $email );
		Helper::update_option( Constant::LASSO_ACCOUNT_USER_ID, $user_id );
		Setting::set_setting( Enum::EMAIL_SUPPORT, $email );

		wp_send_json_success(
			array(
				'connected' => true,
				'synced'    => true,
				'email'     => $email,
				'user_id'   => $user_id,
			)
		);
	}

	/**
	 * Do save support to open intercom chat
	 */
	public function lasso_lite_review_snooze() {
		Helper::verify_access_and_nonce();

		$link_count = SURL::total();

		Helper::update_option( Constant::LASSO_OPTION_REVIEW_SNOOZE, '1' );
		Helper::update_option( Constant::LASSO_OPTION_REVIEW_LINK_COUNT, $link_count );

		wp_send_json_success(
			array(
				'status' => 1,
			)
		);
	}

	/**
	 * Disable Review notification
	 */
	public function lasso_lite_disable_review() {
		Helper::verify_access_and_nonce();

		Helper::update_option( Constant::LASSO_OPTION_REVIEW_ALLOW, '0' );

		wp_send_json_success(
			array(
				'status' => 1,
			)
		);
	}

	/**
	 * Dismiss Performance promotion in the dashboard
	 */
	public function lasso_lite_dismiss_notice() {
		Helper::verify_access_and_nonce();
		$option_name = Helper::POST()['option_name'] ?? Constant::LASSO_OPTION_DISMISS_PERFORMANCE_NOTICE; // phpcs:ignore
		if ( ! in_array( $option_name, array( Constant::LASSO_OPTION_DISMISS_PERFORMANCE_NOTICE, Constant::LASSO_OPTION_DISMISS_PROMOTIONS ), true ) ) {
			wp_send_json_error( 'Invalid option name.' );
		}

		Helper::update_option( $option_name, '1' );

		wp_send_json_success(
			array(
				'status' => 1,
			)
		);
	}

	/**
	 * Disable Affiliate+ Promotions notification
	 */
	public function lasso_lite_disable_affiliate_promotions() {
		Helper::verify_access_and_nonce();

		Helper::update_option( Constant::LASSO_OPTION_AFFILIATE_PROMOTIONS, '0' );

		wp_send_json_success(
			array(
				'status' => 1,
			)
		);
	}
}
