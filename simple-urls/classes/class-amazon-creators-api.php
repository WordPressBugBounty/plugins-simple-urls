<?php
/**
 * Thin Amazon Creators API client (HTTP, no SDK).
 *
 * @package LassoLite\Classes
 *
 * @see https://affiliate-program.amazon.com/creatorsapi/docs/en-us/get-started/using-curl
 */

namespace LassoLite\Classes;

/**
 * Amazon_Creators_Api
 */
class Amazon_Creators_Api {
	const API_BASE = 'https://creatorsapi.amazon';

	const LWA_SCOPE = 'creatorsapi::default';

	const COGNITO_SCOPE = 'creatorsapi/default';

	const DEFAULT_RESOURCES = array(
		'images.primary.large',
		'images.primary.medium',
		'images.primary.small',
		'itemInfo.title',
		'itemInfo.features',
		'offersV2.listings.price',
		'offersV2.listings.availability',
		'parentASIN',
	);

	const SEARCH_RESOURCES = array(
		'images.primary.large',
		'images.primary.medium',
		'images.primary.small',
		'itemInfo.title',
		'offersV2.listings.price',
		'offersV2.listings.availability',
	);

	/**
	 * Fetch product data from an Amazon product URL.
	 *
	 * @param string $url  Amazon product URL.
	 * @param array  $args Optional overrides: country, client_id, client_secret, version, partner_tag, resources, product_only.
	 * @return array{status:string,error_code:string,message:string,product:array,item?:array|null}
	 */
	public static function fetch_product_by_url( $url, $args = array() ) {
		$url = trim( (string) $url );

		if ( '' === $url || ! Amazon_Api::is_amazon_url( $url ) ) {
			return self::shape_response( self::fail_response( 'InvalidAmazonUrl', 'URL is not a valid Amazon product link.' ), $args );
		}

		$asin = Amazon_Api::get_product_id_by_url( $url );
		if ( '' === $asin ) {
			return self::shape_response( self::fail_response( 'AsinNotFound', 'Could not extract ASIN from URL.' ), $args );
		}

		if ( empty( $args['country'] ) ) {
			$lasso_settings  = Setting::get_settings();
			$args['country'] = Amazon_Api::get_country_for_creators_api( $url, $lasso_settings );
		}

		return self::fetch_product( $asin, $args['country'], $args );
	}

	/**
	 * Fetch product data by ASIN and country code.
	 *
	 * @param string $asin    Amazon ASIN.
	 * @param string $country Lasso country code (e.g. us, gb, jp). Falls back to plugin default.
	 * @param array  $args    Optional credential/resource overrides. Set product_only=true to omit raw item.
	 * @return array{status:string,error_code:string,message:string,product:array,item?:array|null}
	 */
	public static function fetch_product( $asin, $country = '', $args = array() ) {
		$asin = strtoupper( trim( (string) $asin ) );

		if ( '' === $asin ) {
			return self::shape_response( self::fail_response( 'InvalidAsin', 'ASIN is required.' ), $args );
		}

		$credentials = self::resolve_credentials( $args );
		if ( isset( $credentials['status'] ) ) {
			return self::shape_response( $credentials, $args );
		}

		$marketplace = self::resolve_marketplace( $country, $args );
		$resources   = ! empty( $args['resources'] ) && is_array( $args['resources'] )
			? $args['resources']
			: self::DEFAULT_RESOURCES;

		$api_result = self::get_items(
			array( $asin ),
			$marketplace,
			$credentials,
			$resources
		);

		if ( 'success' !== $api_result['status'] ) {
			return self::shape_response( $api_result, $args );
		}

		$items = $api_result['items'];
		if ( empty( $items ) ) {
			return self::shape_response( self::fail_response( 'NotFound', 'Product not found.' ), $args );
		}

		$item = is_array( $items[0] ) ? $items[0] : json_decode( wp_json_encode( $items[0] ), true );

		return self::shape_response(
			array(
				'status'     => 'success',
				'error_code' => '',
				'message'    => '',
				'product'    => self::normalize_item( $item, $marketplace ),
				'item'       => $item,
			),
			$args
		);
	}

	/**
	 * Verify Creators credentials by obtaining an access token.
	 *
	 * @param array $args Optional credential overrides: client_id, client_secret, version, partner_tag.
	 * @return array{status:string,error_code:string,message:string,product:array,item:null}
	 */
	public static function verify_credentials( $args = array() ) {
		$credentials = self::resolve_credentials( $args );
		if ( isset( $credentials['status'] ) ) {
			return $credentials;
		}

		$token_result = self::request_access_token( $credentials );
		if ( ! empty( $token_result['token'] ) ) {
			return array(
				'status'     => 'success',
				'error_code' => '',
				'message'    => 'Amazon Creators API credentials verified successfully.',
				'product'    => array(),
				'item'       => null,
			);
		}

		if ( $token_result['status_code'] >= 500 || 0 === $token_result['status_code'] || 429 === $token_result['status_code'] ) {
			return self::fail_response( 'HttpError', 'Service temporarily unavailable.' );
		}

		return self::fail_response( 'AuthFailed', 'Invalid Amazon Creators API credentials.' );
	}

	/**
	 * Call Creators API getItems.
	 *
	 * @param array  $asins       ASIN list.
	 * @param string $marketplace Marketplace domain (e.g. www.amazon.com).
	 * @param array  $credentials Resolved credentials.
	 * @param array  $resources   Resource list.
	 * @return array{status:string,error_code:string,message:string,items:array,raw:object|null}
	 */
	private static function get_items( array $asins, $marketplace, array $credentials, array $resources ) {
		$token = self::get_access_token( $credentials );
		if ( '' === $token ) {
			return self::fail_response( 'AuthFailed', 'Could not obtain Creators API access token.' );
		}

		$headers = array(
			'Authorization' => self::build_authorization_header( $token, $credentials['version'] ),
			'Content-Type'  => 'application/json',
			'x-marketplace' => $marketplace,
		);

		$body = array(
			'itemIds'     => array_values( $asins ),
			'itemIdType'  => 'ASIN',
			'marketplace' => $marketplace,
			'partnerTag'  => $credentials['partner_tag'],
			'resources'   => array_values( $resources ),
		);

		$response = self::http_post_json( self::API_BASE . '/catalog/v1/getItems', $body, $headers );
		if ( ! empty( $response['error'] ) ) {
			return self::fail_response( 'HttpError', $response['error'] );
		}

		$payload = $response['body'];
		if ( $response['status_code'] >= 400 || ! is_object( $payload ) ) {
			$message = is_object( $payload ) && ! empty( $payload->message )
				? (string) $payload->message
				: 'Creators API getItems request failed.';

			return self::fail_response( 'ApiError', $message );
		}

		$items = array();
		if ( isset( $payload->itemsResult->items ) && is_array( $payload->itemsResult->items ) ) {
			$items = $payload->itemsResult->items;
		}

		return array(
			'status'     => 'success',
			'error_code' => '',
			'message'    => '',
			'items'      => $items,
			'raw'        => $payload,
		);
	}

	/**
	 * Fetch and cache OAuth access token.
	 *
	 * @param array $credentials Resolved credentials.
	 * @return string
	 */
	private static function get_access_token( array $credentials ) {
		// Include secret in cache key so credential rotation does not reuse a stale OAuth token.
		$cache_key = 'lasso_lite_creators_token_' . md5(
			$credentials['client_id'] . '|' . $credentials['version'] . '|' . $credentials['client_secret']
		);
		$cached    = get_transient( $cache_key );
		if ( is_string( $cached ) && '' !== $cached ) {
			return $cached;
		}

		$token_result = self::request_access_token( $credentials );
		if ( '' === $token_result['token'] ) {
			return '';
		}

		$ttl = max( 60, intval( $token_result['expires_in'] ) - 60 );
		set_transient( $cache_key, $token_result['token'], $ttl );

		return $token_result['token'];
	}

	/**
	 * Request OAuth access token without reading cache.
	 *
	 * @param array $credentials Resolved credentials.
	 * @return array{token:string,expires_in:int,status_code:int,error:string}
	 */
	private static function request_access_token( array $credentials ) {
		$token_endpoint = self::get_token_endpoint( $credentials['version'] );
		$is_lwa         = self::is_lwa_version( $credentials['version'] );

		if ( $is_lwa ) {
			$response = self::http_post_json(
				$token_endpoint,
				array(
					'grant_type'    => 'client_credentials',
					'client_id'     => $credentials['client_id'],
					'client_secret' => $credentials['client_secret'],
					'scope'         => self::LWA_SCOPE,
				),
				array( 'Content-Type' => 'application/json' )
			);
		} else {
			$response = wp_remote_post(
				$token_endpoint,
				array(
					'timeout' => 30,
					'headers' => array( 'Content-Type' => 'application/x-www-form-urlencoded' ),
					'body'    => array(
						'grant_type'    => 'client_credentials',
						'client_id'     => $credentials['client_id'],
						'client_secret' => $credentials['client_secret'],
						'scope'         => self::COGNITO_SCOPE,
					),
				)
			);

			$response = self::normalize_wp_response( $response );
		}

		if ( ! empty( $response['error'] ) ) {
			return array(
				'token'       => '',
				'expires_in'  => 0,
				'status_code' => 0,
				'error'       => (string) $response['error'],
			);
		}

		$body = $response['body'];
		if ( ! is_object( $body ) || empty( $body->access_token ) ) {
			return array(
				'token'       => '',
				'expires_in'  => 0,
				'status_code' => intval( $response['status_code'] ),
				'error'       => is_object( $body ) && ! empty( $body->error ) ? (string) $body->error : 'missing_access_token',
			);
		}

		return array(
			'token'       => (string) $body->access_token,
			'expires_in'  => isset( $body->expires_in ) ? intval( $body->expires_in ) : 3600,
			'status_code' => intval( $response['status_code'] ),
			'error'       => '',
		);
	}

	/**
	 * Resolve Creators credentials from args or plugin settings.
	 *
	 * @param array $args Request args.
	 * @return array|array{status:string,error_code:string,message:string,product:array,item:null}
	 */
	private static function resolve_credentials( array $args ) {
		$settings = Setting::get_settings();

		$credentials = array(
			'client_id'     => trim( (string) ( $args['client_id'] ?? $settings['amazon_creators_credential_id'] ?? '' ) ),
			'client_secret' => trim( (string) ( $args['client_secret'] ?? $settings['amazon_creators_secret'] ?? '' ) ),
			'version'       => trim( (string) ( $args['version'] ?? $settings['amazon_creators_version'] ?? '' ) ),
			'partner_tag'   => trim( (string) ( $args['partner_tag'] ?? $settings['amazon_creators_partner_tag'] ?? '' ) ),
		);

		foreach ( array( 'client_id', 'client_secret', 'version', 'partner_tag' ) as $field ) {
			if ( '' === $credentials[ $field ] ) {
				return self::fail_response(
					'CredentialsMissing',
					'Amazon Creators API credentials are incomplete. Configure them in Lasso Amazon settings or pass client_id, client_secret, version, and partner_tag.'
				);
			}
		}

		return $credentials;
	}

	/**
	 * Resolve marketplace domain from country code.
	 *
	 * @param string $country Country code.
	 * @param array  $args    Optional marketplace override.
	 * @return string
	 */
	private static function resolve_marketplace( $country, array $args = array() ) {
		if ( ! empty( $args['marketplace'] ) ) {
			return (string) $args['marketplace'];
		}

		$country_key = strtolower( trim( (string) $country ) );
		if ( '' === $country_key ) {
			$settings    = Setting::get_settings();
			$country_key = strtolower( (string) ( $settings['amazon_default_tracking_country'] ?? 'us' ) );
		}

		$countries = Amazon_Api::get_amazon_api_countries();
		if ( ! empty( $countries[ $country_key ]['amazon_domain'] ) ) {
			return (string) $countries[ $country_key ]['amazon_domain'];
		}

		return 'www.amazon.com';
	}

	/**
	 * Map Creators item payload into Lasso-friendly product fields.
	 *
	 * @param array  $item        Creators item.
	 * @param string $marketplace Marketplace domain.
	 * @return array
	 */
	private static function normalize_item( array $item, $marketplace ) {
		$title    = $item['itemInfo']['title']['displayValue'] ?? '';
		$features = $item['itemInfo']['features']['displayValues'] ?? array();
		$features = is_array( $features ) ? $features : array();

		$image = '';
		foreach ( array( 'large', 'medium', 'small' ) as $size ) {
			$url = $item['images']['primary'][ $size ]['url'] ?? '';
			if ( '' !== $url ) {
				$image = $url;
				break;
			}
		}

		$price_data = self::extract_price_data( $item );
		$quantity   = self::extract_quantity( $item );

		return array(
			'product_id'  => (string) ( $item['asin'] ?? '' ),
			'title'       => (string) $title,
			'image'       => (string) $image,
			'price'       => (string) ( $price_data['price'] ?? '' ),
			'amount'      => floatval( $price_data['amount'] ?? 0 ),
			'quantity'    => $quantity,
			'url'         => (string) ( $item['detailPageURL'] ?? '' ),
			'features'    => $features,
			'parent_asin' => (string) ( $item['parentASIN'] ?? '' ),
			'marketplace' => (string) $marketplace,
		);
	}

	/**
	 * Extract display price and numeric amount from offersV2 when present.
	 *
	 * @param array $item Creators item.
	 * @return array{price:string,amount:float}
	 */
	private static function extract_price_data( array $item ) {
		$listings = $item['offersV2']['listings'] ?? array();
		if ( ! is_array( $listings ) || empty( $listings ) ) {
			return array(
				'price'  => '',
				'amount' => 0,
			);
		}

		$listing = is_array( $listings[0] ) ? $listings[0] : json_decode( wp_json_encode( $listings[0] ), true );
		$price   = $listing['price'] ?? array();
		$money   = is_array( $price['money'] ?? null ) ? $price['money'] : array();

		$display = (string) ( $money['displayAmount'] ?? $price['displayAmount'] ?? '' );
		$amount  = 0;

		if ( isset( $money['amount'] ) && '' !== $money['amount'] ) {
			$amount = floatval( $money['amount'] );
		} elseif ( isset( $price['amount'] ) && '' !== $price['amount'] ) {
			$amount = floatval( $price['amount'] );
		} elseif ( '' !== $display ) {
			$amount = floatval( Helper::get_price_value_from_price_text( $display ) );
		}

		return array(
			'price'  => $display,
			'amount' => $amount,
		);
	}

	/**
	 * Infer stock quantity from availability when possible.
	 *
	 * @param array $item Creators item.
	 * @return int
	 */
	private static function extract_quantity( array $item ) {
		$listings = $item['offersV2']['listings'] ?? array();
		if ( ! is_array( $listings ) || empty( $listings ) ) {
			return 0;
		}

		$listing      = is_array( $listings[0] ) ? $listings[0] : json_decode( wp_json_encode( $listings[0] ), true );
		$availability = $listing['availability'] ?? array();
		$type         = strtoupper( (string) ( $availability['type'] ?? '' ) );
		$message      = strtolower( (string) ( $availability['message'] ?? '' ) );

		if ( in_array( $type, array( 'OUT_OF_STOCK', 'UNAVAILABLE', 'UNKNOWN' ), true ) ) {
			return 0;
		}

		if ( in_array( $type, array( 'IN_STOCK', 'IN_STOCK_SCARCE', 'AVAILABLE_DATE', 'LEADTIME', 'PREORDER' ), true ) ) {
			return 200;
		}

		if ( false !== strpos( $message, 'out' ) || false !== strpos( $message, 'unavailable' ) ) {
			return 0;
		}

		return 200;
	}

	/**
	 * Build Authorization header value.
	 *
	 * @param string $token   Access token.
	 * @param string $version Credential version.
	 * @return string
	 */
	private static function build_authorization_header( $token, $version ) {
		if ( self::is_lwa_version( $version ) ) {
			return 'Bearer ' . $token;
		}

		return 'Bearer ' . $token . ', Version ' . $version;
	}

	/**
	 * Whether credential version uses Login with Amazon (3.x).
	 *
	 * @param string $version Credential version.
	 * @return bool
	 */
	private static function is_lwa_version( $version ) {
		return 0 === strpos( (string) $version, '3.' );
	}

	/**
	 * Token endpoint for credential version.
	 *
	 * @param string $version Credential version.
	 * @return string
	 */
	private static function get_token_endpoint( $version ) {
		switch ( (string) $version ) {
			case '2.1':
				return 'https://creatorsapi.auth.us-east-1.amazoncognito.com/oauth2/token';
			case '2.2':
				return 'https://creatorsapi.auth.eu-south-2.amazoncognito.com/oauth2/token';
			case '2.3':
				return 'https://creatorsapi.auth.us-west-2.amazoncognito.com/oauth2/token';
			case '3.2':
				return 'https://api.amazon.co.uk/auth/o2/token';
			case '3.3':
				return 'https://api.amazon.co.jp/auth/o2/token';
			case '3.1':
			default:
				return 'https://api.amazon.com/auth/o2/token';
		}
	}

	/**
	 * POST JSON via wp_remote_post.
	 *
	 * @param string $url     Request URL.
	 * @param array  $body    JSON body.
	 * @param array  $headers Request headers.
	 * @return array{status_code:int,body:object|null,error:string}
	 */
	private static function http_post_json( $url, array $body, array $headers ) {
		$response = wp_remote_post(
			$url,
			array(
				'timeout' => 30,
				'headers' => $headers,
				'body'    => wp_json_encode( $body ),
			)
		);

		return self::normalize_wp_response( $response );
	}

	/**
	 * Normalize wp_remote_* response.
	 *
	 * @param array|\WP_Error $response HTTP response.
	 * @return array{status_code:int,body:object|null,error:string}
	 */
	private static function normalize_wp_response( $response ) {
		if ( is_wp_error( $response ) ) {
			return array(
				'status_code' => 0,
				'body'        => null,
				'error'       => $response->get_error_message(),
			);
		}

		$status_code = intval( wp_remote_retrieve_response_code( $response ) );
		$raw_body    = wp_remote_retrieve_body( $response );
		$body        = json_decode( $raw_body );

		return array(
			'status_code' => $status_code,
			'body'        => is_object( $body ) || is_array( $body ) ? $body : null,
			'error'       => '',
		);
	}

	/**
	 * Build a failure response.
	 *
	 * @param string $error_code Error code.
	 * @param string $message    Error message.
	 * @return array{status:string,error_code:string,message:string,product:array,item:null}
	 */
	private static function fail_response( $error_code, $message ) {
		return array(
			'status'     => 'fail',
			'error_code' => (string) $error_code,
			'message'    => (string) $message,
			'product'    => array(),
			'item'       => null,
		);
	}

	/**
	 * Optionally strip raw Creators item payload from public responses.
	 *
	 * @param array $response API response.
	 * @param array $args     Request args.
	 * @return array
	 */
	private static function shape_response( array $response, array $args ) {
		if ( ! empty( $args['product_only'] ) ) {
			unset( $response['item'] );
		}

		return $response;
	}
}
