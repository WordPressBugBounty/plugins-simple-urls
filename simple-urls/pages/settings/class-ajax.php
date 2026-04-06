<?php
/**
 * Setting General - Ajax.
 *
 * @package Pages
 */

namespace LassoLite\Pages\Settings;

use LassoLite\Admin\Constant;

use LassoLite\Classes\Enum;
use LassoLite\Classes\Helper;
use LassoLite\Classes\License;
use LassoLite\Classes\Page;
use LassoLite\Classes\Setting;

/**
 * Setting General - Ajax.
 */
class Ajax {
	/**
	 * Declare "SURLs ajax requests" to WordPress.
	 */
	public function register_hooks() {
		add_action( 'wp_ajax_lasso_lite_save_settings_amazon', array( $this, 'lasso_lite_save_settings_amazon' ) );
		add_action( 'wp_ajax_lasso_lite_verify_amazon_creators_credentials', array( $this, 'lasso_lite_verify_amazon_creators_credentials' ) );
		add_action( 'wp_ajax_lasso_lite_save_settings_general', array( $this, 'lasso_lite_save_settings_general' ) );
		add_action( 'wp_ajax_lasso_lite_store_settings', array( $this, 'lasso_lite_store_settings' ) );
		add_action( 'wp_ajax_lasso_lite_reactivate_license', array( $this, 'lasso_lite_reactivate_license' ) );
	}

	/**
	 * Add a Field to a Product
	 */
	public function lasso_lite_save_settings_amazon() {
		Helper::verify_access_and_nonce();

		$post             = Helper::POST();
		$current_settings = Setting::get_settings();
		$amazon_tracking_id              = sanitize_text_field( $post['amazon_tracking_id'] ?? '' );
		$amazon_access_key_id            = sanitize_text_field( $post['amazon_access_key_id'] ?? '' );
		$amazon_secret_key               = sanitize_text_field( $post['amazon_secret_key'] ?? '' );
		$amazon_creators_credential_id   = sanitize_text_field( $post['amazon_creators_credential_id'] ?? '' );
		$amazon_creators_secret          = sanitize_text_field( $post['amazon_creators_secret'] ?? '' );
		$amazon_creators_version         = sanitize_text_field( $post['amazon_creators_version'] ?? '' );
		$amazon_creators_partner_tag     = sanitize_text_field( $post['amazon_creators_partner_tag'] ?? '' );
		$amazon_default_tracking_country = sanitize_text_field( $post['amazon_default_tracking_country'] ?? '' );
		$amazon_pricing_daily            = $post['amazon_pricing_daily'] ?? '';
		$auto_monetize_amazon            = $post['auto_monetize_amazon'] ?? '';
		$auto_upgrade_eligible_links     = $post['auto_upgrade_eligible_links'] ?? '';

		$options['amazon_tracking_id']              = $amazon_tracking_id;
		$options['amazon_access_key_id']            = $amazon_access_key_id;
		$options['amazon_secret_key']               = $amazon_secret_key;
		$options['amazon_creators_credential_id']   = $amazon_creators_credential_id;
		$options['amazon_creators_secret']          = $amazon_creators_secret;
		$options['amazon_creators_version']         = $amazon_creators_version;
		$options['amazon_creators_partner_tag']     = $amazon_creators_partner_tag;
		$options['amazon_default_tracking_country'] = $amazon_default_tracking_country;
		$options['amazon_pricing_daily']            = 'on' === $amazon_pricing_daily;
		$options['auto_monetize_amazon']            = 'on' === $auto_monetize_amazon;
		$options['auto_upgrade_eligible_links']     = 'on' === $auto_upgrade_eligible_links;
		Setting::set_settings( $options );
		if ( $this->did_update_amazon_creators_credentials( $current_settings, $options ) && $this->has_complete_amazon_creators_credentials( $options ) ) {
			$this->mark_amazon_credentials_notice_updated();
		}

		if ( ! empty( $amazon_tracking_id ) ) {
			update_option( Enum::SETUP_AMZ_TRACKING_ID, true );
		}

		$data['msg'] = 'All settings saved';
		wp_send_json_success( $data );
	}

	/**
	 * Verify Amazon Creators API credentials against Lambda.
	 */
	public function lasso_lite_verify_amazon_creators_credentials() {
		Helper::verify_access_and_nonce();

		$post = Helper::POST();

		$credential_id       = sanitize_text_field( $post['amazon_creators_credential_id'] ?? '' );
		$secret              = sanitize_text_field( $post['amazon_creators_secret'] ?? '' );
		$credential_version  = sanitize_text_field( $post['amazon_creators_version'] ?? '' );
		$partner_tag         = sanitize_text_field( $post['amazon_creators_partner_tag'] ?? '' );
		$country             = sanitize_text_field( $post['amazon_default_tracking_country'] ?? '' );
		$email               = Helper::get_option( Constant::LASSO_ACCOUNT_EMAIL, '' );
		$normalized_email    = is_string( $email ) ? strtolower( trim( $email ) ) : '';
		$token               = md5( $normalized_email );

		$missing_fields = array();
		if ( empty( $credential_id ) ) {
			$missing_fields[] = 'Credential ID';
		}
		if ( empty( $secret ) ) {
			$missing_fields[] = 'Secret';
		}
		if ( empty( $credential_version ) ) {
			$missing_fields[] = 'Version';
		}
		if ( empty( $partner_tag ) ) {
			$missing_fields[] = 'Partner tag';
		}

		if ( ! empty( $missing_fields ) ) {
			wp_send_json_error(
				array(
					'msg' => 'Missing required fields: ' . implode( ', ', $missing_fields ),
				)
			);
		}

		if ( empty( $normalized_email ) ) {
			wp_send_json_error(
				array(
					'msg' => 'Connect your Lite account to app.getlasso.co before validating Creators API credentials.',
				)
			);
		}

		$headers = Helper::get_headers();
		$headers['token'] = $token;
		$verify_payload = array(
			'credential_id'      => $credential_id,
			'secret'             => $secret,
			'credential_version' => $credential_version,
			'partner_tag'        => $partner_tag,
			'country'            => $country,
		);

		$response = Helper::send_request(
			'post',
			rtrim( Constant::LASSO_LINK, '/' ) . '/amazon/creators/credentials/verify',
			$verify_payload,
			$headers
		);

		$status_code      = intval( $response['status_code'] ?? 500 );
		$response_body    = $response['response'] ?? null;
		$error_message    = $this->get_creators_validate_message( $response_body, 'Unable to verify Creators API credentials.' );
		$success_message  = $this->get_creators_validate_message( $response_body, 'Creators API credentials verified.' );

		if ( $status_code >= 400 || empty( $response_body ) || empty( $response_body->result ) ) {
			wp_send_json_error(
				array(
					'msg'         => $error_message,
					'status_code' => $status_code,
				)
			);
		}

		Setting::set_settings(
			array(
				'amazon_creators_credential_id'   => $credential_id,
				'amazon_creators_secret'          => $secret,
				'amazon_creators_version'         => $credential_version,
				'amazon_creators_partner_tag'     => $partner_tag,
				'amazon_default_tracking_country' => $country,
			)
		);
		$this->mark_amazon_credentials_notice_updated();

		wp_send_json_success(
			array(
				'msg' => $success_message,
			)
		);
	}

	/**
	 * Extract a human-readable Creators API validation message.
	 *
	 * @param mixed  $response_body    Response body from Lambda proxy.
	 * @param string $default_message Default message fallback.
	 * @return string
	 */
	private function get_creators_validate_message( $response_body, $default_message ) {
		return $this->extract_creators_validate_message_from_payload( $response_body, $default_message );
	}

	/**
	 * Recursively extract a human-readable message from a nested/stringified payload.
	 *
	 * @param mixed  $payload Payload from Lambda/FastAPI.
	 * @param string $default_message Default message fallback.
	 * @return string
	 */
	private function extract_creators_validate_message_from_payload( $payload, $default_message ) {
		if ( is_string( $payload ) ) {
			$decoded_payload = json_decode( $payload );
			if ( JSON_ERROR_NONE === json_last_error() && ( is_object( $decoded_payload ) || is_array( $decoded_payload ) ) ) {
				return $this->extract_creators_validate_message_from_payload( $decoded_payload, $default_message );
			}

			return '' !== trim( $payload ) ? $payload : $default_message;
		}

		if ( is_array( $payload ) ) {
			$payload = json_decode( wp_json_encode( $payload ) );
		}

		if ( ! is_object( $payload ) ) {
			return $default_message;
		}

		if ( ! empty( $payload->detail ) ) {
			return $this->extract_creators_validate_message_from_payload( $payload->detail, $default_message );
		}

		$error_type = '';
		if ( ! empty( $payload->type ) && is_string( $payload->type ) ) {
			$error_type = $payload->type;
		}

		if ( 'ThrottleException' === $error_type ) {
			return 'The system is busy right now. Please try again in about 1 minute.';
		}

		if ( ! empty( $payload->message ) ) {
			$message = $this->extract_creators_validate_message_from_payload( $payload->message, $default_message );
			if ( $default_message !== $message || is_string( $payload->message ) ) {
				return $message;
			}
		}

		if ( ! empty( $payload->fieldList[0]->message ) && is_string( $payload->fieldList[0]->message ) ) {
			return $payload->fieldList[0]->message;
		}

		return $default_message;
	}

	/**
	 * Determine whether the submitted request changed any Amazon API credentials.
	 *
	 * @param array $current_settings Existing settings.
	 * @param array $new_settings Submitted settings.
	 * @return bool
	 */
	private function did_update_amazon_creators_credentials( $current_settings, $new_settings ) {
		$credential_fields = array(
			'amazon_creators_credential_id',
			'amazon_creators_secret',
			'amazon_creators_version',
			'amazon_creators_partner_tag',
		);

		foreach ( $credential_fields as $field ) {
			$current_value = trim( (string) ( $current_settings[ $field ] ?? '' ) );
			$new_value     = trim( (string) ( $new_settings[ $field ] ?? '' ) );

			if ( $current_value !== $new_value ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check whether Creators credentials are complete.
	 *
	 * @param array $settings Settings values.
	 * @return bool
	 */
	private function has_complete_amazon_creators_credentials( $settings ) {
		$credential_fields = array(
			'amazon_creators_credential_id',
			'amazon_creators_secret',
			'amazon_creators_version',
			'amazon_creators_partner_tag',
		);

		foreach ( $credential_fields as $field ) {
			if ( '' === trim( (string) ( $settings[ $field ] ?? '' ) ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Permanently close the Amazon credentials update notice after successful migration.
	 */
	private function mark_amazon_credentials_notice_updated() {
		Helper::update_option( Constant::LASSO_OPTION_AMAZON_CREDENTIALS_UPDATED, '1' );
	}

	/**
	 * Save settings general
	 */
	public function lasso_lite_save_settings_general() {
		Helper::verify_access_and_nonce();

		$post                                 = Helper::POST();
		$general_disable_amazon_notifications = $post['general_disable_amazon_notifications'] ?? Constant::DEFAULT_SETTINGS['general_disable_amazon_notifications'];
		$general_disable_tooltip              = $post['general_disable_tooltip'] ?? Constant::DEFAULT_SETTINGS['general_disable_tooltip'];
		$general_disable_notification         = $post['general_disable_notification'] ?? Constant::DEFAULT_SETTINGS['general_disable_notification'];
		$general_enable_new_ui                = $post['general_enable_new_ui'] ?? Constant::DEFAULT_SETTINGS['general_enable_new_ui'];
		$performance_event_tracking           = $post['performance_event_tracking'] ?? Constant::DEFAULT_SETTINGS['performance_event_tracking'];

		$options['general_disable_amazon_notifications'] = $general_disable_amazon_notifications;
		$options['general_disable_tooltip']              = $general_disable_tooltip;
		$options['general_disable_notification']         = $general_disable_notification;
		$options['performance_event_tracking']           = $performance_event_tracking;

		Setting::set_settings( $options );
		$data['msg'] = 'All settings saved';

		// ? disable new UI
		if ( ! $general_enable_new_ui ) {
			update_option( Enum::LASSO_LITE_ACTIVE, 0 ); // ? fix conflict with L.235
			update_option( Enum::SWITCH_TO_NEW_UI, 0 );

			$data['redirect_url'] = Page::get_page_url();
		}

		wp_send_json_success( $data );
	}

	/**
	 * Store Lasso Lite settings
	 */
	public function lasso_lite_store_settings() {
		Helper::verify_access_and_nonce();

		$data = Helper::POST();

		if ( empty( $data['settings'] ) ) {
			wp_send_json_error( 'No settings to save.' );
		}

		// ? User can not change the Disclosure text
		unset( $data['settings']['disclosure_text'] );

		$settings = $data['settings'];
		$options  = $settings;

		// ? Loop and check for checkbox values, convert them to boolean.
		foreach ( $settings as $key => $value ) {
			if ( is_array( $value ) ) {
				$options[ $key ] = $value;
			} elseif ( 'true' === (string) $value ) {
				$options[ $key ] = true;
			} elseif ( 'false' === (string) $value ) {
				$options[ $key ] = false;
			} else {
				$options[ $key ] = trim( $value );
			}
		}

		// ? update settings
		Setting::set_settings( $options );

		wp_send_json_success(
			array(
				'options' => $options,
				'status'  => 1,
			)
		);
	}

	/**
	 * Re-activate license again in Setting page
	 */
	public function lasso_lite_reactivate_license() {
		$data    = Helper::POST();
		$license = $data['license'] ?? '';

		Setting::set_setting( 'license_serial', $license );
		License::lasso_getinfo();

		list($license_status, $error_code, $error_message) = License::check_license( $license );

		wp_send_json_success(
			array(
				'status'        => $license_status,
				'error_code'    => $error_code,
				'error_message' => $error_message,
			)
		);
	} // @codeCoverageIgnore
}
