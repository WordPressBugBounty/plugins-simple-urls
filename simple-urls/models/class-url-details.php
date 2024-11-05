<?php
/**
 * Models
 *
 * @package Models
 */

namespace LassoLite\Models;

use LassoLite\Admin\Constant;
use LassoLite\Classes\Amazon_Api;
use LassoLite\Classes\Meta_Enum;

/**
 * Model
 */
class Url_Details extends Model {

	const META_KEY_URL_WITHOUT_ARGUMENTS = 'url_without_arguments';
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'lasso_lite_url_details';

	/**
	 * Columns of the table
	 *
	 * @var array
	 */
	protected $columns = array(
		'lasso_id',
		'redirect_url',
		'base_domain',
		'is_opportunity',
		'product_id',

		'product_type',
	);

	/**
	 * Primary key of the table
	 *
	 * @var string
	 */
	protected $primary_key = 'lasso_id';

	/**
	 * Create table
	 */
	public function create_table() {
		$columns_sql = '
			lasso_id bigint UNSIGNED NOT NULL,
			redirect_url longtext NOT NULL,
			base_domain varchar(128) NOT NULL,
			is_opportunity tinyint NOT NULL DEFAULT 1,
			product_id varchar(150),
			product_type varchar(20),
			PRIMARY KEY  (lasso_id),
			KEY  ix_base_domain (base_domain)
		';
		$sql         = '
			CREATE TABLE ' . $this->get_table_name() . ' (
				' . $columns_sql . '
			) ' . $this->get_charset_collate();

		return $this->modify_table( $sql, $this->get_table_name() );
	}

	/**
	 * Get lasso url detail object by product id and product_type
	 *
	 * @param string $product_id   Product id.
	 * @param string $product_type Product type. Default is amazon.
	 */
	public static function get_by_product_id_and_type( $product_id, $product_type = Amazon_Api::PRODUCT_TYPE ) {
		if ( ! $product_id ) {
			return null;
		}

		$sql = '
			SELECT lud.*
			FROM ' . self::get_wp_table_name( 'posts' ) . ' AS wpp
				LEFT JOIN ' . ( new self() )->get_table_name() . ' AS lud
				ON wpp.id = lud.lasso_id
			WHERE wpp.post_type = %s 
				AND lud.product_id = %s 
				AND lud.product_type = %s 
				AND wpp.post_status = "publish"
		';

		$prepare = self::prepare( $sql, Constant::LASSO_POST_TYPE, $product_id, $product_type ); // phpcs:ignore
		$result  = self::get_row( $prepare );

		if ( $result ) {
			return ( new self() )->map_properties( $result );
		}

		return null;
	}

	/**
	 * Get amazon shortlink by base domain amzn.to
	 *
	 * @param int $limit Limit. Default is 10.
	 */
	public static function get_amzn_to_shortlinks( $limit = 10 ) {
		$sql     = '
			SELECT DISTINCT lud.lasso_id
			FROM ' . ( new self() )->get_table_name() . ' AS lud
				INNER JOIN ' . self::get_wp_table_name( 'posts' ) . ' AS wp
				ON wp.ID = lud.lasso_id
				LEFT JOIN ' . self::get_wp_table_name( 'postmeta' ) . ' AS pmeta
				ON wp.ID = pmeta.post_id
			WHERE (
					lud.base_domain = %s
					AND wp.post_type = %s
					AND wp.post_status = "publish"
				)
				OR (
					pmeta.meta_key = %s
					AND pmeta.meta_value LIKE %s
				)
			ORDER BY wp.post_modified_gmt DESC
			LIMIT %d
		';
		$prepare = self::prepare( $sql, 'amzn.to', Constant::LASSO_POST_TYPE, Meta_Enum::SURL_REDIRECT, '%amzn.to%', $limit ); // phpcs:ignore
		$result  = self::get_results( $prepare );

		return $result;
	}
}
