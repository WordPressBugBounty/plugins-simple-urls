<?php
/**
 * Declare class Config
 *
 * @package Config
 */

namespace LassoLite\Classes;

use LassoLite\Admin\Constant;
use LassoLite\Classes\Affiliate_Link;
use LassoLite\Classes\Cache_Per_Process;
use LassoLite\Classes\Helper;
use LassoLite\Classes\Setting;

use LassoLite\Libs\Amazon_Api_V5\AwsV5;

use LassoLite\Models\Amazon_Products;

/**
 * Config
 */
class Amazon_Api {

	const OBJECT_KEY                                        = 'lasso_amazon_api';
	const FUNCTION_NAME_GET_LASSO_ID_BY_PRODUCT_ID_AND_TYPE = 'get_lasso_id_by_product_id_and_type';
	const PRODUCT_TYPE                                      = 'amazon';
	const SHORT_LINK_DOMAINS                                = array( 'amzn.com', 'amzn.to' );
	const FILTER_AMAZON_PRODUCT                             = 'filter_amazon_product';
	const TRACKING_ID_REGEX                                 = '^[a-zA-Z0-9-]+-\d{2,3}$';
	const CURRENCY_ISO                                      = array( 'USD', 'AUD', 'CAD', 'EUR', 'MXN', 'CNY', 'JPY', 'INR', 'SEK', 'BRL', 'TRY', 'GBP', 'PLN', 'EGP', 'SGD', 'AED' );
	const VARIATION_PAGE_LIMIT                              = 2;
	const MARKETPLACE_ELIGIBILITY_OPTION                    = 'lasso_lite_marketplace_eligibility';
	const LITE_MARKETPLACE_PRODUCT_PATH                     = '/lite/marketplace/products';

	/**
	 * Get amazon API countries
	 */
	public static function get_amazon_api_countries() {
		return array(
			'us'  => array(
				'name'          => 'United States',
				'amazon_domain' => 'www.amazon.com',
				'pa_endpoint'   => 'webservices.amazon.com',
				'region'        => 'us-east-1',
			),
			'usa' => array(
				'name'          => 'United States',
				'amazon_domain' => 'www.amazon.com',
				'pa_endpoint'   => 'webservices.amazon.com',
				'region'        => 'us-east-1',
			),
			'au'  => array(
				'name'          => 'Australia',
				'amazon_domain' => 'www.amazon.com.au',
				'pa_endpoint'   => 'webservices.amazon.com.au',
				'region'        => 'us-west-2',
			),
			'aus' => array(
				'name'          => 'Australia',
				'amazon_domain' => 'www.amazon.com.au',
				'pa_endpoint'   => 'webservices.amazon.com.au',
				'region'        => 'us-west-2',
			),
			'br'  => array(
				'name'          => 'Brazil',
				'amazon_domain' => 'www.amazon.com.br',
				'pa_endpoint'   => 'webservices.amazon.com.br',
				'region'        => 'us-east-1',
			),
			'bra' => array(
				'name'          => 'Brazil',
				'amazon_domain' => 'www.amazon.com.br',
				'pa_endpoint'   => 'webservices.amazon.com.br',
				'region'        => 'us-east-1',
			),
			'ca'  => array(
				'name'          => 'Canada',
				'amazon_domain' => 'www.amazon.ca',
				'pa_endpoint'   => 'webservices.amazon.ca',
				'region'        => 'us-east-1',
			),
			'can' => array(
				'name'          => 'Canada',
				'amazon_domain' => 'www.amazon.ca',
				'pa_endpoint'   => 'webservices.amazon.ca',
				'region'        => 'us-east-1',
			),
			'cn'  => array(
				'name'          => 'China',
				'amazon_domain' => 'www.amazon.cn',
				'pa_endpoint'   => 'webservices.amazon.cn',
				'region'        => 'us-east-1',
			),
			'chn' => array(
				'name'          => 'China',
				'amazon_domain' => 'www.amazon.cn',
				'pa_endpoint'   => 'webservices.amazon.cn',
				'region'        => 'us-east-1',
			),
			'fr'  => array(
				'name'          => 'France',
				'amazon_domain' => 'www.amazon.fr',
				'pa_endpoint'   => 'webservices.amazon.fr',
				'region'        => 'eu-west-1',
			),
			'fra' => array(
				'name'          => 'France',
				'amazon_domain' => 'www.amazon.fr',
				'pa_endpoint'   => 'webservices.amazon.fr',
				'region'        => 'eu-west-1',
			),
			'de'  => array(
				'name'          => 'Germany',
				'amazon_domain' => 'www.amazon.de',
				'pa_endpoint'   => 'webservices.amazon.de',
				'region'        => 'eu-west-1',
			),
			'deu' => array(
				'name'          => 'Germany',
				'amazon_domain' => 'www.amazon.de',
				'pa_endpoint'   => 'webservices.amazon.de',
				'region'        => 'eu-west-1',
			),
			'in'  => array(
				'name'          => 'India',
				'amazon_domain' => 'www.amazon.in',
				'pa_endpoint'   => 'webservices.amazon.in',
				'region'        => 'eu-west-1',
			),
			'ind' => array(
				'name'          => 'India',
				'amazon_domain' => 'www.amazon.in',
				'pa_endpoint'   => 'webservices.amazon.in',
				'region'        => 'eu-west-1',
			),
			'it'  => array(
				'name'          => 'Italy',
				'amazon_domain' => 'www.amazon.it',
				'pa_endpoint'   => 'webservices.amazon.it',
				'region'        => 'eu-west-1',
			),
			'ita' => array(
				'name'          => 'Italy',
				'amazon_domain' => 'www.amazon.it',
				'pa_endpoint'   => 'webservices.amazon.it',
				'region'        => 'eu-west-1',
			),
			'jp'  => array(
				'name'          => 'Japan',
				'amazon_domain' => 'www.amazon.co.jp',
				'pa_endpoint'   => 'webservices.amazon.co.jp',
				'region'        => 'us-west-2',
			),
			'jpn' => array(
				'name'          => 'Japan',
				'amazon_domain' => 'www.amazon.co.jp',
				'pa_endpoint'   => 'webservices.amazon.co.jp',
				'region'        => 'us-west-2',
			),
			'mx'  => array(
				'name'          => 'Mexico',
				'amazon_domain' => 'www.amazon.com.mx',
				'pa_endpoint'   => 'webservices.amazon.com.mx',
				'region'        => 'us-east-1',
			),
			'mex' => array(
				'name'          => 'Mexico',
				'amazon_domain' => 'www.amazon.com.mx',
				'pa_endpoint'   => 'webservices.amazon.com.mx',
				'region'        => 'us-east-1',
			),
			'nl'  => array(
				'name'          => 'Netherlands',
				'amazon_domain' => 'www.amazon.nl',
				'pa_endpoint'   => 'webservices.amazon.nl',
				'region'        => 'eu-west-1',
			),
			'nld' => array(
				'name'          => 'Netherlands',
				'amazon_domain' => 'www.amazon.nl',
				'pa_endpoint'   => 'webservices.amazon.nl',
				'region'        => 'eu-west-1',
			),
			'se'  => array(
				'name'          => 'Sweden',
				'amazon_domain' => 'www.amazon.se',
				'pa_endpoint'   => 'webservices.amazon.se',
				'region'        => 'us-west-1',
			),
			'sek' => array(
				'name'          => 'Sweden',
				'amazon_domain' => 'www.amazon.se',
				'pa_endpoint'   => 'webservices.amazon.se',
				'region'        => 'us-west-1',
			),
			'sg'  => array(
				'name'          => 'Singapore',
				'amazon_domain' => 'www.amazon.sg',
				'pa_endpoint'   => 'webservices.amazon.sg',
				'region'        => 'us-west-2',
			),
			'sgp' => array(
				'name'          => 'Singapore',
				'amazon_domain' => 'www.amazon.sg',
				'pa_endpoint'   => 'webservices.amazon.sg',
				'region'        => 'us-west-2',
			),
			'es'  => array(
				'name'          => 'Spain',
				'amazon_domain' => 'www.amazon.es',
				'pa_endpoint'   => 'webservices.amazon.es',
				'region'        => 'eu-west-1',
			),
			'esp' => array(
				'name'          => 'Spain',
				'amazon_domain' => 'www.amazon.es',
				'pa_endpoint'   => 'webservices.amazon.es',
				'region'        => 'eu-west-1',
			),
			'tr'  => array(
				'name'          => 'Turkey',
				'amazon_domain' => 'www.amazon.com.tr',
				'pa_endpoint'   => 'webservices.amazon.com.tr',
				'region'        => 'eu-west-1',
			),
			'tur' => array(
				'name'          => 'Turkey',
				'amazon_domain' => 'www.amazon.com.tr',
				'pa_endpoint'   => 'webservices.amazon.com.tr',
				'region'        => 'eu-west-1',
			),
			'ae'  => array(
				'name'          => 'United Arab Emirates',
				'amazon_domain' => 'www.amazon.ae',
				'pa_endpoint'   => 'webservices.amazon.ae',
				'region'        => 'eu-west-1',
			),
			'are' => array(
				'name'          => 'United Arab Emirates',
				'amazon_domain' => 'www.amazon.ae',
				'pa_endpoint'   => 'webservices.amazon.ae',
				'region'        => 'eu-west-1',
			),
			'gb'  => array(
				'name'          => 'United Kingdom',
				'amazon_domain' => 'www.amazon.co.uk',
				'pa_endpoint'   => 'webservices.amazon.co.uk',
				'region'        => 'eu-west-1',
			),
			'gbr' => array(
				'name'          => 'United Kingdom',
				'amazon_domain' => 'www.amazon.co.uk',
				'pa_endpoint'   => 'webservices.amazon.co.uk',
				'region'        => 'eu-west-1',
			),
		);
	}

	/**
	 * Get ignore errors list
	 */
	public static function get_ignore_error_codes() {
		return array(
			'ItemNotAccessible',
			'InvalidParameterValue',
			'AccessDeniedException',
			'AccessDenied',
			'TooManyRequestsException',
			'TooManyRequests',
			'ThrottlingException',
			'RequestThrottled',
			'AWS.ThrottlingException',
			'AWS.RequestThrottled',
			'AWS.AccessDeniedException',
			'UnrecognizedClient',
		);
	}

	/**
	 * Get amazon domains
	 */
	public static function get_domains() {
		return array(
			'amazon.com',           // ? US
			'amazon.ca',            // ? Canada
			'amazon.co.uk',         // ? UK
			'amazon.com.au',        // ? Australia
			'amazon.com.br',        // ? Brazil
			'amazon.com.mx',        // ? Mexico
			'amazon.fr',            // ? France
			'amazon.de',            // ? Germany
			'amazon.it',            // ? Italy
			'amazon.in',            // ? India
			'amazon.es',            // ? Spain
			'amazon.cn',            // ? China
			'amazon.co.jp',         // ? Japan
			'amazon.nl',            // ? Netherlands
			'amazon.se',            // ? Sweden
			'amazon.sg',            // ? Singapore
			'amazon.com.tr',        // ? Turkey
			'amazon.ae',            // ? United Arab Emirates
			'amzn.com',             // ? Short URL
			'amzn.to',              // ? Short URL
			'amazon-adsystem.com',  // ? Amazon Embed
			'smile.amazon.com',      // ? Amazon Smile
		);
	}

	/**
	 * Get amazon link and flag
	 */
	public static function get_aff_link_and_flag() {
		return array(
			'www.amazon.com'    => array(
				'flag'     => '🇺🇸',
				'aff_link' => 'https://affiliate-program.amazon.com/',
			),
			'www.amazon.ca'     => array(
				'flag'     => '🇨🇦',
				'aff_link' => 'https://associates.amazon.ca/',
			),
			'www.amazon.com.br' => array(
				'flag'     => '🇧🇷',
				'aff_link' => 'https://associados.amazon.com.br/',
			),
			'www.amazon.com.mx' => array(
				'flag'     => '🇲🇽',
				'aff_link' => 'https://afiliados.amazon.com.mx/',
			),
			'www.amazon.fr'     => array(
				'flag'     => '🇫🇷',
				'aff_link' => 'https://partenaires.amazon.fr/',
			),
			'www.amazon.de'     => array(
				'flag'     => '🇩🇪',
				'aff_link' => 'https://partnernet.amazon.de/',
			),
			'www.amazon.it'     => array(
				'flag'     => '🇮🇹',
				'aff_link' => 'https://programma-affiliazione.amazon.it/',
			),
			'www.amazon.es'     => array(
				'flag'     => '🇪🇸',
				'aff_link' => 'https://afiliados.amazon.es/',
			),
			'www.amazon.co.uk'  => array(
				'flag'     => '🇬🇧',
				'aff_link' => 'https://affiliate-program.amazon.co.uk/',
			),
			'www.amazon.cn'     => array(
				'flag'     => '🇨🇳',
				'aff_link' => 'https://associates.amazon.cn/',
			),
			'www.amazon.co.jp'  => array(
				'flag'     => '🇯🇵',
				'aff_link' => 'https://affiliate.amazon.co.jp/',
			),
			'www.amazon.in'     => array(
				'flag'     => '🇮🇳',
				'aff_link' => 'https://affiliate-program.amazon.in/',
			),
			'www.amazon.com.au' => array(
				'flag'     => '🇦🇺',
				'aff_link' => 'https://affiliate-program.amazon.com.au/',
			),
		);
	}

	/**
	 * Check whether a url is amazon link or not
	 *
	 * @param string $url Url.
	 */
	public static function is_amazon_url( $url ) {
		if ( empty( $url ) ) {
			return false;
		}

		$domains = self::get_domains();
		$url     = Helper::add_https( $url );

		if ( ! Helper::validate_url( $url ) ) {
			return false;
		}

		$parse_url = wp_parse_url( $url );
		if ( ! isset( $parse_url['host'] ) ) {
			return false;
		}

		$domain = ltrim( $parse_url['host'], 'www.' );

		if ( in_array( $domain, $domains, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check whether a url is amazon link or not
	 *
	 * @param string $url Url.
	 */
	public static function is_amazon_shortened_url( $url ) {
		$is_amazon_url     = self::is_amazon_url( $url );
		$is_shortented_url = strpos( $url, 'amzn.to' ) || strpos( $url, 'amzn.com' );

		return $is_amazon_url && $is_shortented_url;
	}

	/**
	 * Search amazon product
	 *
	 * @param string $keyword Keyword: product name,...
	 */
	public function search_product( $keyword ) {
		$result = $this->get_product_by_keyword_v5( $keyword, 'All' );
		if ( isset( $result->SearchResult->Items ) ) { // phpcs:ignore
			$items    = $result->SearchResult->Items; // phpcs:ignore
			$products = array();

			foreach ( $items as $item ) {
				$product = $this->extract_search_result_v5( $item );
				array_push( $products, $product );
			}

			return $products;

		} elseif ( isset( $result->Errors ) ) { // phpcs:ignore
			return array(
				'error' => $result->Errors, // phpcs:ignore
			);
		}
	}

	/**
	 * Check whether product url has the same domain with Amazon settings
	 *
	 * @param string $product_url Amazon link.
	 */
	public function is_same_domain( $product_url ) {
		if ( '' === $product_url ) {
			return false;
		}

		$amazon_default_tracking_country = Setting::get_setting( 'amazon_default_tracking_country', 'usa' );
		$all_countries                   = self::get_amazon_api_countries();
		$domain                          = $all_countries[ $amazon_default_tracking_country ]['amazon_domain'] ?? 'www.amazon.com';
		$domain                          = str_replace( 'www.', '', $domain );

		return strpos( $product_url, $domain ) !== false;
	}

	/**
	 * Normalize hub/API marketplace_eligible flag from mixed payload shapes.
	 *
	 * @param mixed $value Raw flag value.
	 * @return bool|null True/false when present; null when missing.
	 */
	public static function normalize_marketplace_eligible_flag( $value ) {
		if ( null === $value ) {
			return null;
		}
		if ( is_bool( $value ) ) {
			return $value;
		}
		if ( is_numeric( $value ) ) {
			return (bool) intval( $value );
		}
		if ( is_string( $value ) ) {
			$parsed = filter_var( $value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
			return null === $parsed ? null : $parsed;
		}

		return null;
	}

	/**
	 * Read marketplace_eligible from a hub/BLS product payload (object or array).
	 *
	 * @param array|object|null $payload Product payload.
	 * @return bool|null
	 */
	public static function parse_marketplace_eligible_from_payload( $payload ) {
		if ( null === $payload ) {
			return null;
		}

		// Normalize objects to arrays so hub camelCase keys stay PHPCS-clean.
		if ( is_object( $payload ) ) {
			return self::parse_marketplace_eligible_from_payload( (array) $payload );
		}

		if ( ! is_array( $payload ) ) {
			return null;
		}

		if ( array_key_exists( 'marketplace_eligible', $payload ) ) {
			return self::normalize_marketplace_eligible_flag( $payload['marketplace_eligible'] );
		}
		if ( array_key_exists( 'marketplaceEligible', $payload ) ) {
			return self::normalize_marketplace_eligible_flag( $payload['marketplaceEligible'] );
		}

		if ( isset( $payload['additionalData'] ) ) {
			$additional = $payload['additionalData'];
			if ( is_object( $additional ) ) {
				$additional = (array) $additional;
			}
			if ( is_array( $additional ) && array_key_exists( 'marketplace_eligible', $additional ) ) {
				return self::normalize_marketplace_eligible_flag( $additional['marketplace_eligible'] );
			}
			if ( is_array( $additional ) && array_key_exists( 'marketplaceEligible', $additional ) ) {
				return self::normalize_marketplace_eligible_flag( $additional['marketplaceEligible'] );
			}
		}

		return null;
	}

	/**
	 * Persist eligibility for display/cron without re-calling hub on every page view.
	 *
	 * @param string $product_id Amazon ASIN.
	 * @param bool   $eligible     Eligibility flag.
	 */
	public static function remember_marketplace_eligibility( $product_id, $eligible ) {
		$product_id = (string) $product_id;
		if ( '' === $product_id ) {
			return;
		}

		$map = get_option( self::MARKETPLACE_ELIGIBILITY_OPTION, array() );
		if ( ! is_array( $map ) ) {
			$map = array();
		}

		$map[ $product_id ] = $eligible ? 1 : 0;
		update_option( self::MARKETPLACE_ELIGIBILITY_OPTION, $map, false );
	}

	/**
	 * Read remembered Marketplace eligibility for an ASIN (null when unknown).
	 *
	 * @param string $product_id Amazon ASIN.
	 * @return bool|null
	 */
	public static function get_remembered_marketplace_eligibility( $product_id ) {
		$product_id = (string) $product_id;
		if ( '' === $product_id ) {
			return null;
		}

		$map = get_option( self::MARKETPLACE_ELIGIBILITY_OPTION, array() );
		if ( ! is_array( $map ) || ! array_key_exists( $product_id, $map ) ) {
			return null;
		}

		return (bool) intval( $map[ $product_id ] );
	}

	/**
	 * API base for Lite hub calls. Honors wp-config LASSO_LINK when defined (local FastAPI).
	 *
	 * @return string
	 */
	public static function get_lasso_api_base() {
		return Constant::get_lasso_link();
	}

	/**
	 * Fetch hub Marketplace product payload for an ASIN (includes marketplace_eligible when hub provides it).
	 *
	 * @param string $product_id   Amazon ASIN.
	 * @param string $amz_link     Amazon URL.
	 * @param bool   $bypass_cache Skip per-process cache (Refresh button).
	 * @return array
	 */
	public function fetch_marketplace_product_payload( $product_id, $amz_link = '', $bypass_cache = false ) {
		$product_id = (string) $product_id;
		if ( '' === $product_id ) {
			return array();
		}

		// Free-data catalog is only for unlicensed Lite without Creators API credentials.
		$lasso_settings = Setting::get_settings();
		$license_serial = trim( (string) ( $lasso_settings['license_serial'] ?? '' ) );
		if ( '' !== $license_serial || self::is_amazon_creators_configured( $lasso_settings ) ) {
			return array();
		}

		$url       = $this->resolve_marketplace_catalog_amazon_url( $product_id, $amz_link );
		$cache_key = $this->build_marketplace_product_payload_cache_key( $product_id, $url );
		if ( ! $bypass_cache ) {
			$cached = Cache_Per_Process::get_instance()->get_cache( $cache_key, null );
			if ( null !== $cached && is_array( $cached ) ) {
				return $cached;
			}
		} else {
			Cache_Per_Process::get_instance()->un_set( $cache_key );
		}

		// FastAPI GET /lite/marketplace/products/{asin}?url=…
		// resolve_marketplace_catalog_amazon_url() always yields a non-empty Amazon URL.
		$path        = self::LITE_MARKETPLACE_PRODUCT_PATH . '/' . rawurlencode( $product_id );
		$query       = array( 'url' => $url );
		$request_url = self::get_lasso_api_base() . $path . '?' . http_build_query( $query, '', '&', PHP_QUERY_RFC3986 );
		$res         = Helper::send_request( 'get', $request_url, array(), Helper::get_headers() );

		$payload = array();
		if ( 200 === intval( $res['status_code'] ?? 0 ) && isset( $res['response'] ) ) {
			$response = $res['response'];
			if ( is_object( $response ) && isset( $response->product ) ) {
				$payload = (array) $response->product;
			} elseif ( is_object( $response ) ) {
				$payload = (array) $response;
			} elseif ( is_array( $response ) ) {
				$payload = isset( $response['product'] ) && is_array( $response['product'] )
					? $response['product']
					: $response;
			}
		}

		Cache_Per_Process::get_instance()->set_cache( $cache_key, $payload );

		$eligible = self::parse_marketplace_eligible_from_payload( $payload );
		if ( null !== $eligible ) {
			self::remember_marketplace_eligibility( $product_id, $eligible );
		}

		return $payload;
	}

	/**
	 * Fetch Marketplace product list/search from Lite FastAPI (GET /lite/marketplace/products).
	 *
	 * @param array $args Query args: search|q, country|store, page, limit.
	 * @return array{products:array,total:int,pageSize:int,pageNumber:int,totalPages:int,hasNextPage:bool}
	 */
	public function fetch_marketplace_products_list( $args = array() ) {
		$search = isset( $args['search'] ) ? (string) $args['search'] : '';
		if ( '' === $search && isset( $args['q'] ) ) {
			$search = (string) $args['q'];
		}
		$search = sanitize_text_field( $search );

		$page  = max( 1, intval( $args['page'] ?? 1 ) );
		$limit = max( 1, min( 50, intval( $args['limit'] ?? 20 ) ) );

		$country = '';
		if ( isset( $args['country'] ) && '' !== (string) $args['country'] ) {
			$country = sanitize_text_field( (string) $args['country'] );
		} elseif ( isset( $args['store'] ) && '' !== (string) $args['store'] ) {
			$country = sanitize_text_field( (string) $args['store'] );
		}

		$query = array(
			'page'  => $page,
			'limit' => $limit,
		);
		if ( '' !== $search ) {
			$query['search'] = $search;
			$query['q']      = $search;
		}
		if ( '' !== $country ) {
			$query['country'] = $country;
			$query['store']   = $country;
		}

		$request_url = self::get_lasso_api_base() . self::LITE_MARKETPLACE_PRODUCT_PATH;
		$request_url = $request_url . '?' . http_build_query( $query, '', '&', PHP_QUERY_RFC3986 );
		$res         = Helper::send_request( 'get', $request_url, array(), Helper::get_headers() );

		$empty = array(
			'products'    => array(),
			'total'       => 0,
			'pageSize'    => $limit,
			'pageNumber'  => $page,
			'totalPages'  => 0,
			'hasNextPage' => false,
		);

		if ( 200 !== intval( $res['status_code'] ?? 0 ) || ! isset( $res['response'] ) ) {
			return $empty;
		}

		$response  = $res['response'];
		$data      = null;
		$data_list = array();
		if ( is_object( $response ) && isset( $response->data ) ) {
			$data = $response->data;
		} elseif ( is_array( $response ) && isset( $response['data'] ) ) {
			$data = $response['data'];
		}

		if ( null === $data ) {
			return $empty;
		}

		if ( is_object( $data ) ) {
			$data_list = (array) $data;
		} elseif ( is_array( $data ) ) {
			$data_list = $data;
		}

		$products = array();
		if ( isset( $data_list['products'] ) && is_array( $data_list['products'] ) ) {
			$products = $data_list['products'];
		}

		$normalized_products = array();
		foreach ( $products as $product ) {
			if ( is_object( $product ) ) {
				$product = (array) $product;
			}
			if ( ! is_array( $product ) || empty( $product ) ) {
				continue;
			}
			$normalized_products[] = $product;
		}

		$total = isset( $data_list['total'] ) ? intval( $data_list['total'] ) : count( $normalized_products );

		$page_size = isset( $data_list['pageSize'] ) ? intval( $data_list['pageSize'] ) : $limit;

		$page_number = isset( $data_list['pageNumber'] ) ? intval( $data_list['pageNumber'] ) : $page;

		$total_pages = isset( $data_list['totalPages'] ) ? intval( $data_list['totalPages'] ) : 0;

		$has_next = ! empty( $data_list['hasNextPage'] );

		return array(
			'products'    => $normalized_products,
			'total'       => $total,
			'pageSize'    => $page_size,
			'pageNumber'  => $page_number,
			'totalPages'  => $total_pages,
			'hasNextPage' => $has_next,
		);
	}

	/**
	 * Amazon URL optionally sent to /lite/marketplace/products/{asin} (parse country + ASIN).
	 *
	 * @param string $product_id Amazon ASIN.
	 * @param string $amz_link   Caller-provided Amazon URL.
	 * @return string
	 */
	private function resolve_marketplace_catalog_amazon_url( $product_id, $amz_link = '' ) {
		$url = $amz_link ? $amz_link : $this->get_amazon_link_by_product_id( $product_id, $amz_link );
		if ( ! $url ) {
			$url = 'https://www.amazon.com/dp/' . rawurlencode( (string) $product_id );
		}

		return $url;
	}

	/**
	 * Per-process catalog cache key (ASIN + Amazon store host).
	 *
	 * @param string $product_id  Amazon ASIN.
	 * @param string $catalog_url Resolved Amazon URL for the hub request.
	 * @return string
	 */
	private function build_marketplace_product_payload_cache_key( $product_id, $catalog_url ) {
		$host = wp_parse_url( $catalog_url, PHP_URL_HOST );
		if ( ! is_string( $host ) || '' === $host ) {
			$host = 'www.amazon.com';
		}

		return 'lite_marketplace_product_payload_' . $product_id . '_' . strtolower( $host );
	}

	/**
	 * Map /lite/marketplace/products catalog fields onto Lite Amazon product shape.
	 *
	 * @param array  $payload    Product object from hub.
	 * @param string $product_id ASIN.
	 * @param string $amz_link   Amazon URL.
	 * @return array|null
	 */
	public function map_marketplace_catalog_product_to_amazon_product( $payload, $product_id, $amz_link = '' ) {
		if ( ! is_array( $payload ) || empty( $payload ) ) {
			return null;
		}

		$title = '';
		if ( ! empty( $payload['label'] ) ) {
			$title = (string) $payload['label'];
		} elseif ( ! empty( $payload['productName'] ) ) {
			$title = (string) $payload['productName'];
		} elseif ( ! empty( $payload['product_name'] ) ) {
			$title = (string) $payload['product_name'];
		}
		$title = trim( $title );
		if ( '' === $title ) {
			return null;
		}

		$asin = '';
		if ( ! empty( $payload['asin'] ) ) {
			$asin = (string) $payload['asin'];
		} elseif ( ! empty( $payload['productDetailAsin'] ) ) {
			$asin = (string) $payload['productDetailAsin'];
		}
		$asin = strtoupper( trim( $asin ) );
		if ( '' === $asin ) {
			$asin = strtoupper( trim( (string) $product_id ) );
		}

		$image = '';
		if ( ! empty( $payload['image'] ) ) {
			$image = (string) $payload['image'];
		}

		$target = $amz_link ? $amz_link : '';
		if ( '' === $target && ! empty( $payload['targetURL'] ) ) {
			$target = (string) $payload['targetURL'];
		} elseif ( '' === $target && ! empty( $payload['targetUrl'] ) ) {
			$target = (string) $payload['targetUrl'];
		}
		if ( '' === $target && $asin ) {
			$target = 'https://www.amazon.com/dp/' . $asin;
		}

		$price_raw = '';
		if ( isset( $payload['price'] ) && '' !== $payload['price'] && null !== $payload['price'] ) {
			$price_raw = (string) $payload['price'];
		}
		$symbol = '';
		if ( ! empty( $payload['priceSymbol'] ) ) {
			$symbol = (string) $payload['priceSymbol'];
		} elseif ( ! empty( $payload['currency'] ) ) {
			$symbol = (string) $payload['currency'];
		}
		$display_price = $price_raw;
		if ( '' !== $price_raw && '' !== $symbol && false === strpos( $price_raw, $symbol ) ) {
			$display_price = $symbol . $price_raw;
		}
		$amount = Helper::get_price_value_from_price_text( $price_raw, $symbol );

		return array(
			'product_id'  => $asin,
			'title'       => $title,
			'url'         => $target,
			'default_url' => $target,
			'image'       => trim( $image ),
			'quantity'    => 200,
			'is_prime'    => false,
			'price'       => $display_price,
			'amount'      => $amount,
			'currency'    => $symbol,
			'features'    => array(),
		);
	}

	/**
	 * Prefer Marketplace catalog free-data before Creators / PA-API / BLS.
	 *
	 * @param string      $product_id    ASIN.
	 * @param bool        $store_product Persist locally.
	 * @param bool|string $updated_at    Optional timestamp.
	 * @param string      $amz_link      Amazon URL.
	 * @param bool        $bypass_cache  Bypass per-process catalog cache (Refresh).
	 * @return array|null fetch_product_info shape, or null to fall back.
	 */
	private function fetch_product_info_from_marketplace_catalog( $product_id, $store_product, $updated_at, $amz_link, $bypass_cache = false ) {
		$payload = $this->fetch_marketplace_product_payload( $product_id, $amz_link, $bypass_cache );
		$product = $this->map_marketplace_catalog_product_to_amazon_product( $payload, $product_id, $amz_link );
		if ( null === $product ) {
			return null;
		}

		$product = $this->enrich_fetched_amazon_product( $product, false, $updated_at, $amz_link );

		if ( $store_product ) {
			if ( '' === trim( (string) ( $product['image'] ?? '' ) ) ) {
				return null;
			}
			$store_data = array(
				'product_id'  => $product['product_id'],
				'title'       => $product['title'],
				'price'       => $product['price'],
				'default_url' => $product['default_url'],
				'url'         => $product['url'],
				'image'       => $product['image'],
				'quantity'    => intval( $product['quantity'] ?? 200 ),
				'is_manual'   => 1,
				'currency'    => $product['currency'] ?? '',
			);
			$stored = Amazon_Products::store_marketplace_free_product( $store_data, $updated_at );
			if ( ! $stored ) {
				return null;
			}
		}

		return $this->build_fetch_product_info_response( $product, 'marketplace', $payload, 'success', '' );
	}

	/**
	 * Whether Lite may use the Marketplace/BLS free image+price path for this ASIN.
	 *
	 * @param string $product_id Amazon ASIN.
	 * @param string $amz_link   Amazon URL.
	 * @return bool
	 */
	public function is_marketplace_eligible_for_free_data( $product_id, $amz_link = '' ) {
		$remembered = self::get_remembered_marketplace_eligibility( $product_id );
		if ( null !== $remembered ) {
			return $remembered;
		}

		$payload  = $this->fetch_marketplace_product_payload( $product_id, $amz_link );
		$eligible = self::parse_marketplace_eligible_from_payload( $payload );

		return true === $eligible;
	}

	/**
	 * Fetch amazon product from Amazon API v5
	 *
	 * @param string      $product_id    Amazon product id.
	 * @param bool        $store_product Store product into DB or not. Default to false.
	 * @param bool|string $updated_at    Set date time or not. Default to false.
	 * @param string      $amz_link      Amazon link. Default to empty.
	 * @param bool        $url_version_param Is add version param to request api url to ignore cache. Default to false.
	 * @param bool        $force_bls Force fetch product from BLS or not. Default to false.
	 * @param bool        $refresh_image Bypass cache and fetch fresh product image/metadata. Default to false.
	 * @return mixed
	 */
	public function fetch_product_info( $product_id, $store_product = false, $updated_at = false, $amz_link = '', $url_version_param = false, $force_bls = false, $refresh_image = false ) {
		$lasso_db = new Lasso_DB();

		$lasso_settings       = Setting::get_settings();
		$is_amazon_configured = $lasso_settings['amazon_access_key_id'] && $lasso_settings['amazon_secret_key'] && $lasso_settings['amazon_tracking_id'];
		$license_serial       = trim( (string) ( $lasso_settings['license_serial'] ?? '' ) );
		$use_marketplace_free = '' === $license_serial && ! self::is_amazon_creators_configured( $lasso_settings );

		// Unlicensed Lite without Creators: catalog for add-link/Refresh. Skip pricing workers ($updated_at) and explicit force_bls.
		if (
			$use_marketplace_free
			&& '' !== trim( (string) $product_id )
			&& ! $force_bls
			&& false === $updated_at
		) {
			$catalog_result = $this->fetch_product_info_from_marketplace_catalog(
				$product_id,
				$store_product,
				$updated_at,
				$amz_link,
				(bool) $refresh_image
			);
			if ( null !== $catalog_result ) {
				return $catalog_result;
			}
		}

		if ( $use_marketplace_free && ! $force_bls && '' !== trim( (string) $product_id ) ) {
			if ( $this->is_marketplace_eligible_for_free_data( $product_id, $amz_link ) ) {
				$force_bls = true;
			}
		}

		if (
			self::is_amazon_creators_verified( $lasso_settings )
			&& $this->is_same_domain( $amz_link )
		) {
			$creators_result = $this->fetch_product_info_from_creators_api(
				$product_id,
				$store_product,
				$updated_at,
				$amz_link,
				$lasso_settings
			);
			if ( null !== $creators_result ) {
				return $creators_result;
			}
		}

		$result = ! $force_bls && $is_amazon_configured && $this->is_same_domain( $amz_link ) ? $this->get_product_by_id_v5( $product_id ) : false;
		// phpcs:ignore
		if ( is_object( $result ) && isset( $result->Errors[0] ) && (
				"The ItemId $product_id provided in the request is invalid." === $result->Errors[0]->Message // phpcs:ignore
				|| "The value [$product_id] provided in the request for ItemIds is invalid." === $result->Errors[0]->Message // phpcs:ignore
			)
		) {
			return array(
				'product'    => array(),
				'api'        => 'yes',
				'full_item'  => array(),
				'status'     => 'failed',
				'error_code' => 'NotFound',
			);
		} elseif ( is_object( $result ) && isset( $result->ItemsResult->Items[0] ) ) { // phpcs:ignore
			$item = $result->ItemsResult->Items[0]; // phpcs:ignore

			$product                = $this->extract_search_result_v5( $item, true );
			$product_url            = $amz_link ? $amz_link : $product['url'];
			$product['status_code'] = 200;
			$product['url']         = self::get_amazon_product_url( $product_url );

			// ? If $item->Offers is missing, we try to get the quantity from variation data
			if ( ! isset( $item->Offers ) ) { // phpcs:ignore
				sleep( 1 ); // ? Delay for a while before call the next request
				$variation_product = $this->get_product_variation( $product_id, $product_url );
				if ( $variation_product && isset( $variation_product['quantity'] ) && $variation_product['quantity'] ) {
					$product = $variation_product;
				}
			}

			$product = $this->enrich_fetched_amazon_product( $product, $store_product, $updated_at, $amz_link );

			return array(
				'product'    => $product,
				'api'        => 'yes',
				'full_item'  => $item,
				'status'     => 'success',
				'error_code' => '',
			);
		} elseif ( '' !== $license_serial || $force_bls ) {
			if ( ! $this->is_marketplace_eligible_for_free_data( $product_id, $amz_link ) ) {
				return array(
					'product'    => array(),
					'api'        => 'no',
					'full_item'  => array(),
					'status'     => 'failed',
					'error_code' => 'NotFound',
				);
			}

			list( $product, $status ) = $this->fetch_product_from_bls( $product_id, $store_product, $updated_at, $amz_link, $url_version_param, $force_bls, $refresh_image );

			return array(
				'product'    => $product,
				'api'        => 'no',
				'full_item'  => array(),
				'status'     => 200 === $status ? 'success' : 'fail',
				'error_code' => 404 === $status ? 'NotFound' : '',
			);
		}

		return array(
			'product'    => array(),
			'api'        => 'no',
			'full_item'  => array(),
			'status'     => 'failed',
			'error_code' => 'NotFound',
		);
	}

	/**
	 * Fetch amazon product from BLS (Lambda)
	 *
	 * @param string      $product_id    Amazon product id.
	 * @param bool        $store_product Store product into DB or not. Default to false.
	 * @param bool|string $updated_at    Set date time or not. Default to false.
	 * @param string      $amz_link      Amazon link. Default to empty.
	 * @param bool        $url_version_param Is add version param to request api url to ignore cache. Default to false.
	 * @param bool        $force_bls Force fetch product from BLS or not. Default to false.
	 * @param bool        $refresh_image Bypass cache and fetch fresh product image/metadata. Default to false.
	 */
	public function fetch_product_from_bls( $product_id, $store_product = false, $updated_at = false, $amz_link = '', $url_version_param = false, $force_bls = false, $refresh_image = false ) {
		$url    = strpos( $amz_link, 'amazon.' ) !== false ? $amz_link : $this->get_amazon_link_by_product_id( $product_id, $amz_link );
		$m_link = self::get_amazon_product_url( $url );
		$url    = self::get_amazon_product_url( $url, false );

		$amazon_product = array(
			'title'           => '',
			'image'           => '',
			'url'             => $m_link,
			'price'           => '',
			'currency'        => '',
			'savings_amount'  => 0,
			'savings_percent' => 0,
			'savings_basis'   => 0,
		);

		try {
			$res = Helper::get_url_status_code_by_broken_link_service( $url, true, false, $url_version_param, $force_bls, $refresh_image );
		} catch ( \Throwable $e ) {
			$res = array(
				'status_code' => 500,
				'response'    => null,
			);
		}

		$bls_response = ( isset( $res['response'] ) && is_object( $res['response'] ) ) ? $res['response'] : null;
		$bls_status   = ( null !== $bls_response && isset( $bls_response->status ) ) ? intval( $bls_response->status ) : 0;

		if ( 200 === $res['status_code'] && 200 === $bls_status ) {
			$img_url      = $bls_response->imgUrl ?? '';
			$img_url      = '' !== $img_url ? $img_url : '';
			$product_name = $bls_response->productName ?? '';
			$product_name = '' === $product_name ? ( $bls_response->pageTitle ?? '' ) : $product_name;
			$quantity     = $bls_response->quantity ?? 200;
			$price        = $bls_response->price ?? '';
			$product_id   = self::get_product_id_by_url( $url );

			$temp_url = $bls_response->finalUrl ?? $url;
			$url      = '' !== $temp_url ? $temp_url : $url;
			$m_link   = self::get_amazon_product_url( $amz_link ? $amz_link : $url );

			if ( strpos( $product_name, 'Amazon.com:' ) === 0 ) {
				$product_name = str_replace( 'Amazon.com:', '', $product_name );
				$product_name = trim( $product_name );
			}

			$bls_eligible = self::parse_marketplace_eligible_from_payload( $bls_response );
			if ( null === $bls_eligible ) {
				$bls_eligible = $this->is_marketplace_eligible_for_free_data( $product_id, $amz_link );
			} else {
				self::remember_marketplace_eligibility( $product_id, $bls_eligible );
			}

			// Persist any successful BLS product payload (eligibility gates opening this path, not storage).
			if ( $product_id && $store_product ) {
				$store_data = array(
					'product_id'  => $product_id,
					'title'       => $product_name,
					'price'       => $price,
					'default_url' => $url,
					'url'         => $m_link,
					'image'       => trim( $img_url ),
					'quantity'    => intval( $quantity ),  // Manual checks won't show out of stock for now. TODO: Add BLS to out of stock checks.
					'is_manual'   => 1,
				);

				// ? Set additional data for amazon product
				if ( isset( $bls_response->additionalData ) && ! empty( $bls_response->additionalData ) ) {
					$basis_price    = $bls_response->additionalData->basis_price ?? '';
					$basis_price    = Helper::get_price_value_from_price_text( $basis_price );
					$savings_amount = $bls_response->additionalData->saving_amount ?? '';
					$savings_amount = Helper::get_price_value_from_price_text( $savings_amount );

					$store_data['currency']        = $bls_response->additionalData->currency_name ?? '';
					$store_data['savings_basis']   = $basis_price;
					$store_data['savings_amount']  = $savings_amount;
					$store_data['savings_percent'] = $bls_response->additionalData->saving_amount_percent ?? '';

					$amazon_product['currency']        = $store_data['currency'];
					$amazon_product['savings_basis']   = $store_data['savings_basis'];
					$amazon_product['savings_amount']  = $store_data['savings_amount'];
					$amazon_product['savings_percent'] = $store_data['savings_percent'];
				}

				Amazon_Products::store_marketplace_free_product( $store_data, $updated_at );
			}

			$amazon_product['title']       = $product_name;
			$amazon_product['image']       = $img_url;
			$amazon_product['url']         = $m_link;
			$amazon_product['price']       = $price;
			$amazon_product['quantity']    = $quantity;
			$amazon_product['status_code'] = $bls_status;
		}
		if ( 404 === $res['status_code'] || ( 200 === $res['status_code'] && 404 === $bls_status ) ) {
			$amz_model    = new Amazon_Products();
			$last_updated = gmdate( 'Y-m-d H:i:s', time() );
			$amz_model->update_amazon_field( $product_id, 'last_updated', $last_updated );
			$amz_model->update_amazon_field( $product_id, 'out_of_stock', 0 );
		}

		if ( $bls_status ) {
			$status = $bls_status;
		} elseif ( 200 === intval( $res['status_code'] ?? 0 ) ) {
			$status = 500;
		} else {
			$status = intval( $res['status_code'] ?? 500 );
		}

		return array( $amazon_product, intval( $status ) );
	}

	/**
	 * Insert or Update Amazon Product Data
	 *
	 * @param array       $product        Amazon product.
	 * @param bool|string $updated_at     Set update date time. Default to false.
	 * @param bool        $allow_partial  When true, allow empty image (BLS may omit imgUrl). Default false.
	 */
	public function update_amazon_product_in_db( $product, $updated_at = false, $allow_partial = false ) {
		global $wpdb;

		$lasso_db = new Lasso_DB();

		$amazon_id            = $product['product_id'] ?? '';
		$default_product_name = $product['title'] ?? '';
		$latest_price         = $product['price'] ?? '';
		$latest_price         = '0' === $latest_price || ( is_int( $latest_price ) && 0 === $latest_price ) ? '' : $latest_price;
		$base_url             = self::get_amazon_product_url( $product['default_url'] ?? '', false, false );
		$monetized_url        = self::get_amazon_product_url( $product['url'] ?? '', true, false );
		$default_image        = trim( $product['image'] ?? '' );
		$existing_product     = $amazon_id ? $this->get_amazon_product_from_db( $amazon_id ) : false;
		if ( Amazon_Products::product_has_customer_price_override( $amazon_id ) && is_array( $existing_product ) ) {
			$latest_price = $existing_product['latest_price'] ?? $latest_price;
		}
		if ( Amazon_Products::product_has_customer_image_override( $amazon_id ) && is_array( $existing_product ) ) {
			$default_image = $existing_product['default_image'] ?? $default_image;
		}
		$last_updated         = gmdate( 'Y-m-d H:i:s', time() );
		$last_updated         = $updated_at ? $updated_at : $last_updated;
		$is_prime             = $product['is_prime'] ?? '';
		$currency             = $product['currency'] ?? '';
		$features             = wp_json_encode( $product['features'] ?? array() );
		$savings_amount       = $product['savings_amount'] ?? '';
		$savings_percent      = $product['savings_percent'] ?? '';
		$savings_basis        = $product['savings_basis'] ?? '';
		$is_manual            = $product['is_manual'] ?? 0;
		$quantity             = intval( $product['quantity'] ?? 200 );
		$out_of_stock         = 0 === $quantity ? 1 : 0;

		$image_required = ! $allow_partial;
		if ( '' === $amazon_id || '' === $default_product_name
			|| ( $image_required && '' === $default_image )
			|| ( '' !== $default_image && Helper::validate_url( $default_image ) === false && strpos( $default_image, 'data:image' ) !== 0 )
		) {
			return false;
		}

		$base_url      = trim( $base_url );
		$monetized_url = trim( $monetized_url );

		$query   = '
            INSERT INTO ' . $lasso_db->amazon_products . "
                (
                    amazon_id, default_product_name, latest_price, base_url, 
                    monetized_url, default_image, last_updated, is_prime, 
                    currency, features, savings_amount, savings_percent, 
                    savings_basis, is_manual, out_of_stock
                )
            VALUES
                (
                    %s, %s, %s, %s, 
                    %s, %s, %s, %d, 
                    %s, %s, %s, %d,
                    %s, %d, %d
                )
            ON DUPLICATE KEY UPDATE
                amazon_id = %s,
                default_product_name = %s,
                latest_price = %s,
                base_url = %s,
                monetized_url = %s,
                default_image = (CASE WHEN %s='' or %s IS NULL THEN `default_image` ELSE %s END),
                last_updated = %s,
                is_prime = %d,
                currency  = %s,
                features = %s,
                savings_amount = %s,
                savings_percent = %d,
                savings_basis = %s,
                is_manual = %d,
                out_of_stock = %d
            ;
		";
		$prepare = $wpdb->prepare(
		// phpcs:ignore
			$query,
			// ? First for insert
			$amazon_id,
			$default_product_name,
			$latest_price,
			$base_url,
			$monetized_url,
			$default_image,
			$last_updated,
			$is_prime,
			$currency,
			$features,
			$savings_amount,
			$savings_percent,
			$savings_basis,
			$is_manual,
			$out_of_stock,
			// ? Second for update
			$amazon_id,
			$default_product_name,
			$latest_price,
			$base_url,
			$monetized_url,
			$default_image,
			$default_image,
			$default_image,
			$last_updated,
			$is_prime,
			$currency,
			$features,
			$savings_amount,
			$savings_percent,
			$savings_basis,
			$is_manual,
			$out_of_stock
		);

		$lasso_db->query( $prepare );

		return true;
	}

	/**
	 * Get amazon product from DB
	 *
	 * @param string $product_id Amazon Product id.
	 */
	public function get_amazon_product_from_db( $product_id ) {
		if ( empty( $product_id ) ) {
			return false;
		}

		global $wpdb;

		$lasso_db = new Lasso_DB();

		$sql = '
			SELECT * 
			FROM ' . $lasso_db->amazon_products . ' 
			WHERE amazon_id = %s
		';

		$prepare = $wpdb->prepare( $sql, $product_id ); // phpcs:ignore
		$result  = $lasso_db->get_row( $prepare, ARRAY_A );

		if ( $result ) {
			$result                  = apply_filters( self::FILTER_AMAZON_PRODUCT, $result );
			$result['monetized_url'] = self::get_amazon_product_url( $result['monetized_url'] );
			$result['base_url']      = self::get_amazon_product_url( $result['base_url'], false );
			$result['features']      = json_decode( $result['features'] );
		}

		return $result;
	}

	/**
	 * Extract result to an array
	 *
	 * @param object $response    Data from Amazon.
	 * @param bool   $large_image Get large image size. Default to false.
	 * @param string $product_id  Product Id. Default to empty.
	 * @param string $product_url Product url. Default to empty.
	 *
	 * @return array
	 */
	private function extract_search_result_v5( $response, $large_image = false, $product_id = '', $product_url = '' ) {
		$image = '';
		if ( isset( $response->Images->Primary ) ) { // phpcs:ignore
			$image = $large_image ? $response->Images->Primary->Large->URL : $response->Images->Primary->Small->URL; // phpcs:ignore
		}

		// @codingStandardsIgnoreStart
		$result = array(
			'product_id'      => $product_id ? $product_id : ( $response->ASIN ?? 0 ),
			'title'           => $response->ItemInfo->Title->DisplayValue ?? '',
			'url'             => $product_url ? $product_url : ( $response->DetailPageURL ?? '' ),
			'default_url'     => $response->DetailPageURL ?? '',
			'image'           => $image,
			'quantity'        => $response->Offers->Summaries[0]->OfferCount ?? 0,
			'is_prime'        => $response->Offers->Listings[0]->DeliveryInfo->IsPrimeEligible ?? false,
			'price'           => $response->Offers->Listings[0]->Price->DisplayAmount ?? 0,
			'amount'          => $response->Offers->Listings[0]->Price->Amount ?? 0,
			'currency'        => $response->Offers->Listings[0]->Price->Currency ?? '',
			'features'        => $response->ItemInfo->Features->DisplayValues ?? array(),
			'savings_amount'  => $response->Offers->Listings[0]->Price->Savings->Amount ?? 0.0,
			'savings_percent' => $response->Offers->Listings[0]->Price->Savings->Percentage ?? 0,
			'savings_basis'   => $response->Offers->Listings[0]->SavingBasis->Amount ?? 0.0,
		);
		// @codingStandardsIgnoreEnd

		return $result;
	}

	/**
	 * Query amazon v5
	 *
	 * @param array   $parameters      Amazon API params.
	 * @param boolean $lasso_settings Lasso settings. Default to false.
	 *
	 * @return array
	 */
	public function query_amazon_v5( $parameters, $lasso_settings = false ) {
		try {
			if ( ! $lasso_settings ) {
				$lasso_settings = Setting::get_settings();
			}

			$this->amazon_access_key_id = $lasso_settings['amazon_access_key_id'] ?? '';
			$this->amazon_secret_key    = $lasso_settings['amazon_secret_key'] ?? '';
			$this->amazon_tracking_id   = $lasso_settings['amazon_tracking_id'] ?? '';

			$result = $this->aws_signed_request_v5( $parameters, $this->amazon_access_key_id, $this->amazon_secret_key, $this->amazon_tracking_id );

			if ( isset( $result->Errors ) ) { // phpcs:ignore
				$error = $result->Errors[0]; // phpcs:ignore
			}

			return $result;
		} catch ( \Exception $e ) {
			return array();
		}
	}

	/**
	 * Get Amazon product by product id
	 *
	 * @param string $product_id Amazon product id.
	 *
	 * @return object
	 */
	public function get_product_by_id_v5( $product_id ) {
		$parameters = array(
			'Operation' => 'GetItems',
			'ItemIds'   => array( $product_id ),
			'Resources' => array(
				'Images.Primary.Small',
				'Images.Primary.Large',
				'ItemInfo.Title',
				'ItemInfo.ContentRating',
				'ItemInfo.Features',
				'ItemInfo.ProductInfo',
				'ItemInfo.TechnicalInfo',
				'Offers.Listings.Price',
				'Offers.Listings.SavingBasis',
				'Offers.Summaries.OfferCount',
				'Offers.Listings.DeliveryInfo.IsPrimeEligible',
			),
		);

		$json_response = $this->query_amazon_v5( $parameters );

		return $json_response;
	}

	/**
	 * Get product from Amazon by product name
	 *
	 * @param string $keyword      Keyword.
	 * @param string $product_type Product type.
	 *
	 * @return object
	 */
	public function get_product_by_keyword_v5( $keyword, $product_type ) {
		$parameters = array(
			'Operation'   => 'SearchItems',
			'Keywords'    => $keyword,
			'SearchIndex' => $product_type,
			'Resources'   => array(
				'Images.Primary.Small',
				'Images.Primary.Large',
				'ItemInfo.Title',
				'ItemInfo.ContentRating',
				'ItemInfo.Features',
				'ItemInfo.ProductInfo',
				'ItemInfo.TechnicalInfo',
				'Offers.Listings.Price',
				'Offers.Listings.SavingBasis',
				'Offers.Summaries.OfferCount',
				'Offers.Listings.DeliveryInfo.IsPrimeEligible',
			),
		);

		$json_response = $this->query_amazon_v5( $parameters );

		return $json_response;
	}

	/**
	 * Sign request v5
	 *
	 * @param array  $params               Amazon params.
	 * @param string $amazon_access_key_id Amazon access key.
	 * @param string $amazon_secret_key    Amazon secret key.
	 * @param string $amazon_tracking_id   Amazon tracking id.
	 *
	 * @return object|bool
	 */
	private function aws_signed_request_v5( $params, $amazon_access_key_id, $amazon_secret_key, $amazon_tracking_id ) {
		// phpcs:ignore
		// $amazon_domain = 'www.amazon.com';
		// $pa_endpoint = 'webservices.amazon.com';

		$country       = Setting::get_setting( 'amazon_default_tracking_country', 'usa' );
		$countries     = self::get_amazon_api_countries();
		$amazon_domain = $countries[ $country ]['amazon_domain'];
		$pa_endpoint   = $countries[ $country ]['pa_endpoint'];
		$amazon_region = $countries[ $country ]['region'];

		$params['Marketplace'] = $amazon_domain;
		$params['PartnerType'] = 'Associates';
		$params['PartnerTag']  = $amazon_tracking_id;
		$post_fields           = wp_json_encode( $params );

		$aws_v5 = new AwsV5( $amazon_access_key_id, $amazon_secret_key );
		$aws_v5->setHost( $pa_endpoint );
		$aws_v5->setRegionName( $amazon_region );
		$aws_v5->setPayload( $post_fields );
		$aws_v5->addHeader( 'x-amz-target', 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.' . $params['Operation'] );
		$headers = $aws_v5->getHeaders( true );
		$url     = "https://$pa_endpoint/paapi5/searchitems";

		// @codingStandardsIgnoreStart
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_fields );
		curl_setopt( $ch, CURLOPT_POST, 1 );

		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

		$result = curl_exec( $ch );
		if ( curl_errno( $ch ) ) {
			// phpcs:ignore
			// $error = curl_error( $ch );
			return false;
		}
		curl_close( $ch );
		// @codingStandardsIgnoreEnd

		return json_decode( $result );
	}

	/**
	 * Get Amazon product id by url
	 *
	 * @param string $url Amazon link.
	 * @return string|bool
	 */
	public static function get_product_id_by_url( $url ) {
		$url = Helper::add_https( $url );

		if ( ! self::is_amazon_url( $url ) || strpos( $url, '.' ) === false ) {
			return false;
		}

		$parse_url           = wp_parse_url( $url );
		$amazon_domain       = trim( $parse_url['host'] ?? '', 'www.' );
		$amazon_domain_regex = str_replace( '.', '\.', str_replace( 'www.', '', $amazon_domain ) );

		$reg     = '#(?:https?://(?:www\.){0,1}' . $amazon_domain_regex . '(?:/.*){0,1}(?:/dp/|/gp/product/|/ASIN/|/gp/video/detail/))([a-zA-Z0-9]*)(.*?)(?:/.*|$)#';
		$matches = array();
		preg_match( $reg, $url, $matches );

		return isset( $matches[1] ) && ! empty( $matches[1] ) ? $matches[1] : false;
	}

	/**
	 * Keep URL arguments for Amazon URL
	 *
	 * @param string $product_url Amazon product url.
	 *
	 * @return array $results
	 */
	private static function keep_args( $product_url ) {
		// ? amazon link but it is not product url
		if ( ! self::is_amazon_url( $product_url ) || self::is_amazon_shortened_url( $product_url ) ) {
			return array();
		}

		$results    = array();
		$url_params = array(
			'maas',
			'ref_',
			's',
			'aa_campaignid',
			'aa_creativeid',
			'aa_adgroupid',
			'campaignId',
			'linkCode',
			'linkId',
		);

		foreach ( $url_params as $param_name ) {
			$param_value = Helper::get_argument_from_url( $product_url, $param_name );
			if ( $param_value ) {
				$results[] = $param_name . '=' . $param_value;
			}
		}

		return $results;
	}

	/**
	 * Get amazon tracking id by url
	 * This function will be deprecated in the future. Please use Helper::get_argument_from_url() instead
	 *
	 * @param string $link Amazon link.
	 * @return string
	 */
	public static function get_amazon_tracking_id_by_url( $link ) {
		$search  = '/([?|&|&amp;|\/]{1})tag=([a-zA-Z0-9\-\_]*)/i';
		$matches = array();
		preg_match( $search, $link, $matches );

		return $matches[2] ?? '';
	}

	/**
	 * Clean Amazon URL that contains maas parameter
	 *
	 * @param string $product_url Amazon product URL.
	 * @return string
	 */
	public static function clean_maas_url( $product_url ) {
		return $product_url;
	}

	/**
	 * Make product url to shorten url if this setting is enabled
	 *
	 * @param string $product_url        Amazon product url.
	 * @param bool   $monetize           Monetize link or not. Default to true.
	 * @param bool   $check_lasso_post   Check Lasso post or ignore. Default to true.
	 * @param string $custom_tracking_id Allow to use custom tracking id.
	 */
	public static function get_amazon_product_url( $product_url, $monetize = true, $check_lasso_post = true, $custom_tracking_id = '' ) {
		// ? amazon link but it is not product url
		if ( ! self::is_amazon_url( $product_url ) || self::is_amazon_shortened_url( $product_url ) ) {
			return $product_url;
		}

		// If MAAS param exists and the original tag is exactly "maas", leave the URL untouched
		$__maas_arg = Helper::get_argument_from_url( $product_url, 'maas' );
		$__orig_tag = Helper::get_argument_from_url( $product_url, 'tag' );
		if ( $__maas_arg && strtolower( $__orig_tag ) === 'maas' ) {
			return $product_url;
		}

		$product_id = self::get_product_id_by_url( $product_url );

		// ? remove all url queries, just keep needed args
		$url_without_params = explode( '?', $product_url )[0];
		if ( $product_id && ! $monetize ) {
			$keep_args = self::keep_args( $product_url );
			$args      = Helper::build_url_parameter_string( $keep_args );

			$product_url = $args ? $url_without_params . '?' . $args : $url_without_params;
			$product_url = self::clean_maas_url( $product_url );

			return $product_url;
		}

		$lasso_settings                        = Setting::get_settings();
		$amazon_tracking_id                    = trim( $lasso_settings['amazon_tracking_id'] ?? '' );
		$amazon_add_tracking_id_to_attribution = $lasso_settings['amazon_add_tracking_id_to_attribution'] ?? true;

		$lasso_db      = new Lasso_DB();
		$amz_cache_key = self::OBJECT_KEY . '_' . self::FUNCTION_NAME_GET_LASSO_ID_BY_PRODUCT_ID_AND_TYPE . '_' . $product_id . '_' . self::PRODUCT_TYPE;
		$lasso_id      = Cache_Per_Process::get_instance()->get_cache( $amz_cache_key, null );
		if ( null === $lasso_id ) {
			$lasso_id = $lasso_db->get_lasso_id_by_product_id_and_type( $product_id, self::PRODUCT_TYPE );
			Cache_Per_Process::get_instance()->set_cache( $amz_cache_key, $lasso_id );
		}

		if ( $check_lasso_post && ! $lasso_id ) {
			$product_url = self::clean_maas_url( $product_url );
			return $product_url;
		}

		$tag = Helper::get_argument_from_url( $product_url, 'tag' );
		$tag = $custom_tracking_id ? $custom_tracking_id : $tag;
		$tag = $tag ? $tag : '';
		$tag = ! empty( $tag ) ? $tag : $amazon_tracking_id;

		// ? Return the remove all url queries for product url
		if ( $product_id ) {
			$tag_args       = $tag ? 'tag=' . $tag : '';
			$keep_args      = self::keep_args( $product_url );
			$maas           = Helper::get_argument_from_url( $product_url, 'maas' );
			$add_tag_to_url = ( $maas && $amazon_add_tracking_id_to_attribution ) || ! $maas;

			if ( $add_tag_to_url ) {
				array_unshift( $keep_args, $tag_args );
			}

			$args        = Helper::build_url_parameter_string( $keep_args );
			$product_url = $args ? $url_without_params . '?' . $args : $url_without_params;
		} else {
			$product_url = str_replace( '&amp;', '&', $product_url );
			$parse       = wp_parse_url( $product_url );
			parse_str( $parse['query'] ?? '', $query );

			// ? set tag id (tracking id) at the end of the url
			if ( $tag ) {
				$query['tag'] = $tag;
			} elseif ( ! empty( $amazon_tracking_id ) ) {
				$query['tag'] = $amazon_tracking_id;
			}

			if ( ! $monetize ) {
				unset( $query['tag'] );
			}

			$parse['query'] = Helper::get_query_from_array( $query );
			$product_url    = Helper::get_url_from_parse( $parse );
			$product_url    = trim( $product_url );
			$product_url    = trim( $product_url, '?' );
		}

		$product_url = self::clean_maas_url( $product_url );

		return $product_url;
	}

	/**
	 * Get Amazon product info from url accept url or post_id
	 *
	 * @param string|int $url_or_post_id URL or post id.
	 */
	public function get_amazon_product( $url_or_post_id ) {

		if ( is_numeric( $url_or_post_id ) ) {
			// ? get amazon product using post_id
			$post_id   = $url_or_post_id;
			$amazon_id = Affiliate_Link::get_amazon_id( $post_id );
			$product   = $this->get_amazon_product_from_db( $amazon_id );
			return $product;
		} else {
			$url = $url_or_post_id;
		}

		// ? get amazon prodcut using url
		if ( empty( $url ) ) {
			return '';
		}

		$url            = trim( $url, '/' );
		$product_id     = self::get_product_id_by_url( $url );
		$product        = $this->fetch_product_info( $product_id, true ); // ? Let's save all Amazon details as well
		$amazon_product = '';

		if ( 'success' === $product['status'] ) {
			$product        = $product['product'];
			$shorten_url    = self::get_amazon_product_url( $product['url'] );
			$amazon_product = array(
				'id'          => $product['product_id'],
				'name'        => $product['title'],
				'price'       => $product['price'],
				'url'         => $shorten_url,
				'image'       => $product['image'],
				'description' => '',
			);
		}

		return $amazon_product;
	}

	/**
	 * Check whether a URL is amazon search page
	 *
	 * @param string $url URL.
	 *
	 * @return bool|string
	 */
	public static function is_amazon_search_page( $url ) {
		$amazon_id      = self::get_product_id_by_url( $url );
		$parse          = wp_parse_url( $url );
		$path           = $parse['path'] ?? '';
		$path           = rtrim( $path, '/' );
		$keywords       = Helper::get_argument_from_url( $url, 'keywords' );
		$field_keywords = Helper::get_argument_from_url( $url, 'field-keywords' );
		$k              = Helper::get_argument_from_url( $url, 'k' );
		$k              = $k ? $k : $field_keywords;
		$k              = $k ? $k : $keywords;

		if ( ! $amazon_id && ( '/s' === substr( $path, -2 ) || strpos( $path, '/s/' ) !== false ) && $k ) {
			return $k;
		}

		return false;
	}

	/**
	 * Check whether a URL is amazon search page
	 *
	 * @param string $url URL.
	 *
	 * @return bool|string
	 */
	public static function get_search_page_title( $url ) {
		$new_title = 'Amazon';
		$k         = self::is_amazon_search_page( $url );
		if ( $k ) {
			$base_domain  = Helper::get_base_domain( $url );
			$title_prefix = ucfirst( $base_domain );
			$new_title    = $title_prefix . ' : ' . $k;
		}

		return $new_title;
	}

	/**
	 * Validate Amazon tracking id
	 *
	 * @param string $tracking_id Amazon tracking id.
	 * @return boolean
	 */
	public static function validate_tracking_id( $tracking_id ) {
		return (bool) preg_match( '/' . self::TRACKING_ID_REGEX . '/i', $tracking_id );
	}

	/**
	 * Get Amazon link by product id
	 *
	 * @param string $product_id Amazon product id.
	 * @param string $amz_link   Amazon link. Default to empty.
	 */
	public function get_amazon_link_by_product_id( $product_id, $amz_link = '' ) {
		if ( ! $product_id ) {
			return $amz_link;
		}

		if ( '' !== $amz_link ) {
			$parse = wp_parse_url( $amz_link );
			$host  = $parse['host'] ?? '';
			if ( '' !== $host ) {
				return 'https://' . $host . '/dp/' . $product_id;
			}
		}

		$country       = Setting::get_setting( 'amazon_default_tracking_country', 'usa' );
		$countries     = self::get_amazon_api_countries();
		$amazon_domain = $countries[ $country ]['amazon_domain'];

		return 'https://' . $amazon_domain . '/dp/' . $product_id;
	}

	/**
	 * Check whether a URL is Amazon redirect page
	 *
	 * @param string $url Amazon URL.
	 */
	public static function is_amazon_redirect_page( $url ) {
		if ( ! self::is_amazon_url( $url ) || strpos( $url, '/gp/slredirect/' ) === false ) {
			return false;
		}

		return true;
	}

	/**
	 * Get redirect url
	 *
	 * @param string $url Amazon URL.
	 */
	public static function get_redirect_url( $url ) {
		if ( self::is_amazon_redirect_page( $url ) ) {
			$url_param     = Helper::get_argument_from_url( $url, 'url' );
			$amazon_domain = Helper::get_base_domain( $url );
			$amazon_domain = Helper::add_https( $amazon_domain );
			$new_url       = $amazon_domain . $url_param;
			$product_id    = self::get_product_id_by_url( $new_url );

			if ( $product_id ) {
				$url = $new_url;
			}
		}

		return $url;
	}

	/**
	 * Format price
	 * Ex: convert 19.89USD to $19.89
	 *
	 * @param string $price        Price.
	 * @param string $currency_iso Currency ISO.
	 * @return string
	 */
	public static function format_price( $price, $currency_iso = null ) {
		$currency_iso = $currency_iso ? $currency_iso : self::get_currency_iso_from_price_text( $price );

		if ( $price && $currency_iso ) {
			return self::build_price_with_currency_iso( $price, $currency_iso );
		}

		return $price;
	}

	/**
	 * Get Currency ISO from price text
	 *
	 * @param string $price Price text.
	 * @return mixed|string
	 */
	public static function get_currency_iso_from_price_text( $price ) {
		$result = '';

		foreach ( self::CURRENCY_ISO as $currency_iso ) {
			if ( strpos( $price, $currency_iso ) !== false ) {
				return $currency_iso;
			}
		}

		return $result;
	}

	/**
	 * Build price final format base on the currency ISO
	 *
	 * @param string $price_value  Price value.
	 * @param string $currency_iso Currency ISO.
	 * @return string
	 */
	public static function build_price_with_currency_iso( $price_value, $currency_iso ) {
		$currency_symbol   = Helper::get_currency_symbol_from_iso_code( $currency_iso );
		$price_without_iso = str_replace( $currency_iso, '', $price_value );
		$price_value       = Helper::get_price_value_from_price_text( $price_without_iso, $currency_symbol );
		$currency_position = preg_match( '/[€]|R\$|TL|kr|zł/', $currency_symbol ) ? 'end' : 'begin';
		$price_format      = preg_match( '/[€]|R\$|TL|kr|zł/', $currency_symbol ) ? number_format( $price_value, 2, ',', '.' ) : number_format( $price_value, 2, '.', ',' );

		return 'begin' === $currency_position ? $currency_symbol . $price_format : $price_format . $currency_symbol;
	}

	/**
	 * Get the variation item in stock
	 *
	 * @param string $product_id     Product id.
	 * @param string $product_url    Product url.
	 * @param int    $variation_page Variation page.
	 * @return array
	 */
	public function get_product_variation( $product_id, $product_url, $variation_page = 1 ) {
		$result = $this->get_product_variations_by_id_v5( $product_id, $variation_page );
		$items  = $result->VariationsResult->Items ?? array(); // phpcs:ignore

		if ( ! empty( $items ) ) {
			$items              = $result->VariationsResult->Items; // phpcs:ignore
			$product_variations = array();

			// ? Get product variation list
			foreach ( $items as $item ) {
				$product_variations[] = $this->extract_search_result_v5( $item, true, $product_id, $product_url );
			}

			// ? Sort price from lowest to highest
			usort(
				$product_variations,
				function( $a, $b ) {
					return strcmp( $a['amount'], $b['amount'] );
				}
			);

			// ? Get the in-stock product
			foreach ( $product_variations as $product_variation ) {
				if ( $product_variation['quantity'] && $product_variation['price'] ) {
					return $product_variation;
				}
			}

			// ? If all variation products in this page unavailable, we request to the next page
			$page_count = $result->VariationsResult->VariationSummary->PageCount ?? 1; // phpcs:ignore
			if ( $variation_page < self::VARIATION_PAGE_LIMIT && $variation_page < $page_count ) {
				sleep( 1 ); // ? Delay for a while before call the next request
				return $this->get_product_variation( $product_id, $product_url, $variation_page + 1 );
			}
		}

		return array();
	}

	/**
	 * Get Amazon product variations by product id
	 *
	 * @param string $product_id     Amazon product id.
	 * @param int    $variation_page Variation page.
	 *
	 * @return object
	 */
	public function get_product_variations_by_id_v5( $product_id, $variation_page = 1 ) {
		$parameters = array(
			'Operation'     => 'GetVariations',
			'ASIN'          => $product_id,
			'Condition'     => 'New',
			'VariationPage' => $variation_page,
			'Resources'     => array(
				'Images.Primary.Small',
				'Images.Primary.Large',
				'ItemInfo.Title',
				'ItemInfo.ContentRating',
				'ItemInfo.Features',
				'ItemInfo.ProductInfo',
				'ItemInfo.TechnicalInfo',
				'Offers.Listings.Price',
				'Offers.Listings.SavingBasis',
				'Offers.Summaries.OfferCount',
				'Offers.Listings.DeliveryInfo.IsPrimeEligible',
			),
		);

		$json_response = $this->query_amazon_v5( $parameters );

		return $json_response;
	}

	/**
	 * Build discount pricing html.
	 *
	 * @param string $latest_price Latest price.
	 * @param mixed  $basis_price  Basis price value.
	 * @param string $currency     Currency ISO.
	 */
	public static function build_discount_pricing_html( $latest_price, $basis_price, $currency ) {
		$result = '';

		try {
			$latest_price_value = Helper::get_price_value_from_price_text( $latest_price );
			$basis_price_value  = Helper::get_price_value_from_price_text( $basis_price );

			if ( $basis_price_value && ( round( $latest_price_value, 2 ) < round( $basis_price_value, 2 ) ) ) {
				$currency_symbol    = Helper::get_currency_symbol_from_iso_code( $currency );
				$currency_position  = preg_match( '/[€]|R\$|TL|kr|zł/', $latest_price ) ? 'end' : 'begin';
				$basis_price_format = preg_match( '/[€]|R\$|TL|kr|zł/', $latest_price ) ? number_format( $basis_price_value, 2, ',', '.' ) : number_format( $basis_price_value, 2, '.', ',' );
				$format_price       = 'begin' === $currency_position ? $currency_symbol . $basis_price_format : $basis_price_format . ' ' . $currency_symbol;

				$result = "<strike>$format_price</strike>";
			}
		} catch ( \Exception $e ) {
			$result = '';
		}

		return $result;
	}

	/**
	 * Check amazon setting is configured or not
	 *
	 * @return boolean
	 */
	public static function is_amazon_setting_configured() {
		$lasso_settings       = Setting::get_settings();
		$is_amazon_configured = $lasso_settings['amazon_access_key_id'] && $lasso_settings['amazon_secret_key'] && $lasso_settings['amazon_tracking_id'];

		return $is_amazon_configured || self::is_amazon_creators_verified( $lasso_settings );
	}

	/**
	 * Whether Refresh may run for an Amazon ASIN without PA-API/Creators (Marketplace-only).
	 *
	 * @param string $product_id  Amazon ASIN.
	 * @param string $product_url Amazon product URL.
	 * @return bool
	 */
	public static function is_amazon_refresh_allowed_for_product( $product_id, $product_url = '' ) {
		if ( self::is_amazon_setting_configured() ) {
			return true;
		}

		$product_id = trim( (string) $product_id );
		if ( '' === $product_id ) {
			return false;
		}

		$api = new self();

		return $api->is_marketplace_eligible_for_free_data( $product_id, $product_url );
	}

	/**
	 * Whether Amazon Creators API credentials are complete in settings.
	 *
	 * @param array|false $lasso_settings Settings array. Default false loads current settings.
	 * @return bool
	 */
	public static function is_amazon_creators_configured( $lasso_settings = false ) {
		if ( ! is_array( $lasso_settings ) ) {
			$lasso_settings = Setting::get_settings();
		}

		$fields = array(
			'amazon_creators_credential_id',
			'amazon_creators_secret',
			'amazon_creators_version',
			'amazon_creators_partner_tag',
		);

		foreach ( $fields as $field ) {
			if ( '' === trim( (string) ( $lasso_settings[ $field ] ?? '' ) ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Whether Creators credentials were verified successfully (signature matches stored settings).
	 *
	 * @param array|false $lasso_settings Settings array. Default false loads current settings.
	 * @return bool
	 */
	public static function is_amazon_creators_verified( $lasso_settings = false ) {
		if ( ! self::is_amazon_creators_configured( $lasso_settings ) ) {
			return false;
		}

		if ( ! is_array( $lasso_settings ) ) {
			$lasso_settings = Setting::get_settings();
		}

		$current_signature = self::get_amazon_creators_signature( $lasso_settings );
		$saved_signature   = (string) Helper::get_option( Constant::LASSO_OPTION_AMAZON_CREATORS_VERIFIED_SIGNATURE, '' );

		return '' !== $current_signature && '' !== $saved_signature && hash_equals( $saved_signature, $current_signature );
	}

	/**
	 * Build a stable signature for the Creators payload.
	 *
	 * @param array $settings Settings values.
	 * @return string Empty when Creators credentials are incomplete.
	 */
	public static function get_amazon_creators_signature( $settings ) {
		if ( ! self::is_amazon_creators_configured( $settings ) ) {
			return '';
		}

		return hash(
			'sha256',
			wp_json_encode(
				array(
					'credential_id'      => (string) ( $settings['amazon_creators_credential_id'] ?? '' ),
					'secret'             => (string) ( $settings['amazon_creators_secret'] ?? '' ),
					'credential_version' => (string) ( $settings['amazon_creators_version'] ?? '' ),
					'partner_tag'        => (string) ( $settings['amazon_creators_partner_tag'] ?? '' ),
					'country'            => (string) ( $settings['amazon_default_tracking_country'] ?? '' ),
				)
			)
		);
	}

	/**
	 * Resolve Creators API country from an Amazon URL or default tracking country.
	 *
	 * @param string      $amazon_url     Amazon product URL (optional).
	 * @param array|false $lasso_settings Settings array. Default false loads current settings.
	 * @return string
	 */
	public static function get_country_for_creators_api( $amazon_url, $lasso_settings = false ) {
		if ( ! is_array( $lasso_settings ) ) {
			$lasso_settings = Setting::get_settings();
		}

		if ( $amazon_url && self::is_amazon_url( $amazon_url ) ) {
			$base_domain = strtolower( Helper::get_base_domain( $amazon_url ) );
			if ( '' !== $base_domain ) {
				foreach ( self::get_amazon_api_countries() as $country_code => $country_data ) {
					$amazon_domain = strtolower( Helper::get_base_domain( (string) ( $country_data['amazon_domain'] ?? '' ) ) );
					if ( $base_domain === $amazon_domain ) {
						return (string) $country_code;
					}
				}
			}
		}

		return (string) ( $lasso_settings['amazon_default_tracking_country'] ?? '' );
	}

	/**
	 * Whether the Lite install is linked to a Lasso account email.
	 *
	 * @return bool
	 */
	public static function is_lite_account_connected() {
		$email = Helper::get_option( Constant::LASSO_ACCOUNT_EMAIL, '' );

		return is_string( $email ) && '' !== trim( $email );
	}

	/**
	 * Fetch product data via direct Amazon Creators API (replaces deprecated PA-API GetItems).
	 *
	 * @param string      $product_id     Amazon ASIN.
	 * @param bool        $store_product  Store in local DB.
	 * @param bool|string $updated_at     Optional updated timestamp.
	 * @param string      $amz_link       Amazon URL.
	 * @param array       $lasso_settings Plugin settings.
	 * @return array|null Same shape as fetch_product_info success/failure, or null to fall back.
	 */
	private function fetch_product_info_from_creators_api( $product_id, $store_product, $updated_at, $amz_link, $lasso_settings ) {
		if ( ! self::is_amazon_creators_configured( $lasso_settings ) ) {
			return null;
		}

		$api_result = Amazon_Creators_Api::fetch_product(
			$product_id,
			self::get_country_for_creators_api( $amz_link, $lasso_settings ),
			array(
				'product_only' => false,
			)
		);

		if ( 'fail' === ( $api_result['status'] ?? '' ) ) {
			$error_code = (string) ( $api_result['error_code'] ?? '' );

			if ( 'NotFound' === $error_code ) {
				// Fall through to PA-API; BLS free-data path applies its own eligibility gate.
				return null;
			}

			return null;
		}

		$product_data = $api_result['product'] ?? array();
		if ( ! is_array( $product_data ) || empty( $product_data ) ) {
			return null;
		}

		$full_item = $api_result['item'] ?? array();
		$product   = $this->finalize_fetch_product_info_product(
			$product_data,
			$product_id,
			$store_product,
			$updated_at,
			$amz_link
		);

		return $this->build_fetch_product_info_response( $product, 'yes', $full_item, 'success', '' );
	}

	/**
	 * Build fetch_product_info() return array (same contract as get_product_by_id_v5 path).
	 *
	 * @param array       $product     Product fields.
	 * @param string      $api         API source flag.
	 * @param array|object $full_item  Raw item payload.
	 * @param string      $status      success|failed.
	 * @param string      $error_code  Error code.
	 * @return array
	 */
	private function build_fetch_product_info_response( $product, $api, $full_item, $status, $error_code ) {
		return array(
			'product'    => $product,
			'api'        => $api,
			'full_item'  => $full_item,
			'status'     => $status,
			'error_code' => $error_code,
		);
	}

	/**
	 * Apply the same post-processing as the PA-API branch in fetch_product_info().
	 *
	 * @param array       $product       Product fields from API.
	 * @param string      $product_id    Amazon ASIN.
	 * @param bool        $store_product Store in local DB.
	 * @param bool|string $updated_at    Optional updated timestamp.
	 * @param string      $amz_link      Amazon URL.
	 * @return array
	 */
	private function finalize_fetch_product_info_product( $product, $product_id, $store_product, $updated_at, $amz_link ) {
		if ( empty( $product['product_id'] ) ) {
			$product['product_id'] = $product_id;
		}

		return $this->enrich_fetched_amazon_product( $product, $store_product, $updated_at, $amz_link );
	}

	/**
	 * Shared post-fetch enrichment for PA-API and Creators product payloads.
	 *
	 * @param array       $product       Product fields.
	 * @param bool        $store_product Store in local DB.
	 * @param bool|string $updated_at    Optional updated timestamp.
	 * @param string      $amz_link      Amazon URL.
	 * @return array
	 */
	private function enrich_fetched_amazon_product( $product, $store_product, $updated_at, $amz_link ) {
		global $wpdb;

		$lasso_db               = new Lasso_DB();
		$product_url            = $amz_link ? $amz_link : ( $product['url'] ?? '' );
		$product['status_code'] = 200;
		$product['url']         = self::get_amazon_product_url( $product_url );

		$amazon_product_id = (string) ( $product['product_id'] ?? '' );
		$query             = $wpdb->prepare(
			'SELECT post_id FROM ' . $lasso_db->postmeta . " WHERE meta_key = 'amazon_product_id' AND meta_value = %s",
			$amazon_product_id
		);
		$lasso_id            = $lasso_db->get_var( $query );
		$product['lasso_id'] = ( isset( $lasso_id ) ) ? $lasso_id : 0;

		if ( $store_product ) {
			$amazon_tracking_id     = Setting::get_setting( 'amazon_tracking_id', '' );
			$product['default_url'] = '' === $amazon_tracking_id ? $amz_link : ( $product['default_url'] ?? '' );
			$this->update_amazon_product_in_db( $product, $updated_at );
		}

		return $product;
	}

	/**
	 * Get final url of the amazon shortlink from cache
	 *
	 * @param string $shortlink Amazon shortlink.
	 * @return string|null
	 */
	public static function get_shortlink_final_url_cached( $shortlink ) {
		if ( ! self::is_amazon_shortened_url( $shortlink ) ) {
			return null;
		}
		$final_url_cache = get_option( self::build_shortlink_cache_key( $shortlink ) );

		if ( $final_url_cache === $shortlink ) {
			return null;
		}

		return $final_url_cache ? $final_url_cache : null;
	}

	/**
	 * Build amazon shortlink cache key
	 *
	 * @param string $shortlink Amazon shortlink.
	 * @return string|null
	 */
	public static function build_shortlink_cache_key( $shortlink ) {
		if ( ! self::is_amazon_shortened_url( $shortlink ) ) {
			return null;
		}

		$parse = wp_parse_url( $shortlink );
		$host  = str_replace( '.', '_', $parse['host'] );
		$id    = trim( $parse['path'] ?? '', '/' );

		return $host . '_' . $id;
	}

	/**
	 * Format Amazon URLs
	 *
	 * @param string $url Amazon product url.
	 * @return string URL.
	 */
	public static function format_amazon_url( $url ) {
		$is_amazon_link = self::is_amazon_url( $url );
		$product_id     = self::get_product_id_by_url( $url );

		if ( $is_amazon_link && $product_id && strpos( $url, 'smile.amazon.' ) !== false ) {
			$url = str_replace( 'smile.amazon.', 'amazon.', $url );
		}

		return $url;
	}
}
