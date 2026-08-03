<?php
/**
 * URL formatting utilities extracted from Helper.
 *
 * @package Helper
 */

namespace LassoLite\Classes\Helper;

/**
 * Url_Format
 */
class Url_Format {

	/**
	 * Whether URL has http/https scheme.
	 *
	 * @param string $url URL.
	 */
	public static function has_protocol( $url ) {
		if ( strpos( $url, 'http' ) === 0 ) {
			return true;
		}
		return false;
	}

	/**
	 * Add https to the url.
	 *
	 * @param string $url URL.
	 */
	public static function add_https( $url ) {
		$invalid_url = array(
			'https://%20https:/',
			'https://xhttps://',
			'http:/https://',
			'http://https://',
			'https://https://',
			'https://hhttps://',
			'https://]https://',
			'https://&quot;https://',
			'[gift_item link=&quot;https://',
			']https://',
		);
		$url         = trim( $url );
		$url         = str_replace( $invalid_url, 'https://', $url );

		if ( strpos( $url, 'mailto:' ) !== false || filter_var( $url, FILTER_VALIDATE_EMAIL ) ) {
			$email = explode( 'mailto:', $url )[1] ?? '';
			if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
				$url = 'mailto:' . $email;
			}

			return $url;
		}

		if ( '' === $url || strpos( $url, '[' ) === 0 ) {
			return $url;
		}

		if ( strpos( $url, 'http://' ) !== 0 && strpos( $url, 'https://' ) !== 0 && strpos( $url, '.' ) !== false && '#' !== $url ) {
			$url = 'https://' . $url;
		}

		return $url;
	}

	/**
	 * Validate URL.
	 *
	 * @param string $url URL.
	 */
	public static function validate_url( $url ) {
		if ( ! is_string( $url ) ) {
			return false;
		}

		$url = str_replace( ' ', '%20', $url );
		$url = preg_replace( '/[^\00-\255]+/u', '', $url );

		return ( ( strpos( $url, 'http://' ) === 0 || strpos( $url, 'https://' ) === 0 ) &&
			filter_var( $url, FILTER_VALIDATE_URL ) !== false );
	}
}
