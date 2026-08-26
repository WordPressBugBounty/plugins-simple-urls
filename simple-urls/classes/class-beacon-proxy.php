<?php
/**
 * First-party beacon allow/deny (EasyPrivacy). No WordPress I/O.
 *
 * @package LassoLite
 */

namespace LassoLite\Classes;

/**
 * Plan whether a browser beacon should be forwarded.
 */
class Beacon_Proxy {
	const UPSTREAM  = 'https://codedrink.com/js/e';
	const MAX_BYTES = 16384;

	/**
	 * Read the request body with a hard byte cap (MAX_BYTES + 1).
	 *
	 * @param resource|null $stream Optional stream for tests; defaults to php://input.
	 * @return string
	 */
	public static function read_bounded_body( $stream = null ) {
		$owns_stream = null === $stream;
		if ( $owns_stream ) {
			$stream = fopen( 'php://input', 'rb' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen
		}
		if ( false === $stream ) {
			return '';
		}

		$chunk = fread( $stream, self::MAX_BYTES + 1 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fread
		if ( $owns_stream ) {
			fclose( $stream ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
		}

		return is_string( $chunk ) ? $chunk : '';
	}

	/**
	 * Decide whether to forward a beacon.
	 *
	 * @param string $method    HTTP method.
	 * @param string $raw_body  Raw request body.
	 * @return array{ok:bool,status:int,upstream:?string,body?:string}
	 */
	public static function plan( $method, $raw_body ) {
		if ( 'POST' !== strtoupper( (string) $method ) ) {
			return array(
				'ok'       => false,
				'status'   => 405,
				'upstream' => null,
			);
		}

		if ( ! is_string( $raw_body ) || '' === $raw_body || strlen( $raw_body ) > self::MAX_BYTES ) {
			return array(
				'ok'       => false,
				'status'   => 400,
				'upstream' => null,
			);
		}

		json_decode( $raw_body );
		if ( JSON_ERROR_NONE !== json_last_error() ) {
			return array(
				'ok'       => false,
				'status'   => 400,
				'upstream' => null,
			);
		}

		return array(
			'ok'       => true,
			'status'   => 200,
			'upstream' => self::UPSTREAM,
			'body'     => $raw_body,
		);
	}
}
