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

use LassoLite\Models\Url_Details;

/**
 * Model
 */
class Amazon_Products extends Model {
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'lasso_lite_amazon_products';

	/**
	 * Columns of the table
	 *
	 * @var array
	 */
	protected $columns = array(
		'amazon_id',
		'default_product_name',
		'latest_price',
		'base_url',
		'monetized_url',

		'default_image',
		'out_of_stock',
		'is_prime',
		'currency',
		'savings_amount',

		'savings_percent',
		'savings_basis',
		'features',
		'is_manual',
		'last_updated',
	);

	/**
	 * Primary key of the table
	 *
	 * @var string
	 */
	protected $primary_key = 'amazon_id';

	/**
	 * Create table
	 */
	public function create_table() {
		$columns_sql = '
			amazon_id varchar(20) NOT NULL,
			default_product_name varchar(2000) NOT NULL,
			latest_price varchar(32) NULL,
			base_url varchar(2000) NOT NULL,
			monetized_url varchar(2000) NULL,
			default_image varchar(10000) NULL,
			out_of_stock TINYINT(1) NULL DEFAULT 0,
			is_prime TINYINT(1) NOT NULL DEFAULT 0,
			currency VARCHAR(5) NOT NULL DEFAULT \'USD\',
			savings_amount VARCHAR(10) NULL,
			savings_percent INT(10) NULL,
			savings_basis VARCHAR(10) NULL,
			features TEXT NULL,
			is_manual TINYINT(1) NOT NULL DEFAULT 0,
			rating VARCHAR(10) NULL DEFAULT 0,
			reviews INT(11) NULL DEFAULT 0,
			last_updated datetime NOT NULL,
			PRIMARY KEY  (amazon_id)
		';
		$sql         = '
			CREATE TABLE ' . $this->get_table_name() . ' (
				' . $columns_sql . '
			) ' . $this->get_charset_collate();

		return $this->modify_table( $sql, $this->get_table_name() );
	}

	/**
	 * Count total products in wp_lasso_amazon_products table
	 */
	public static function count_amazon_product_in_db() {
		$amz_tbl = self::get_sql_query_amazon_products_need_to_be_updated();
		$mysql   = '
			select count(amz_tbl.amazon_id) as count
			from (' . $amz_tbl . ') as amz_tbl
		';

		$result = Model::get_col( $mysql );

		return intval( $result[0] ?? 0 );
	}

	/**
	 * Get mysql query to get amazon products need to be update pricing
	 */
	public static function get_sql_query_amazon_products_need_to_be_updated() {
		$now             = gmdate( 'Y-m-d H:i:s', time() );
		$posts           = Model::get_wp_table_name( 'posts' );
		$amazon_products = new Amazon_Products();
		$url_details     = new Url_Details();
		$sql             = '
			SELECT 
				ap.*
			FROM ' . $url_details->get_table_name() . ' AS ud
				INNER JOIN ' . $posts . ' AS p
					ON ud.lasso_id = p.ID
				INNER JOIN ' . $amazon_products->get_table_name() . ' AS ap
					ON ap.amazon_id = ud.product_id
			WHERE p.post_type = %s
				AND p.post_status = %s
				AND ud.product_type = %s
				AND ap.last_updated < DATE_SUB(%s, INTERVAL 24 HOUR)
			ORDER BY 
				ap.last_updated ASC
		';
		$prepare         = Model::prepare( $sql, Constant::LASSO_POST_TYPE, 'publish', 'amazon', $now );

		return $prepare;
	}

	/**
	 * Get products in wp_lasso_amazon_products table
	 *
	 * @param int $limit Number of results. Default to 100.
	 */
	public static function get_amazon_product_in_db( $limit = 100 ) {
		$mysql   = self::get_sql_query_amazon_products_need_to_be_updated();
		$mysql  .= '
			LIMIT %d
		';
		$prepare = Model::prepare( $mysql, $limit ); // phpcs:ignore

		return Model::get_results( $prepare, ARRAY_A );
	}

	/**
	 * Update a field for an Amazon product
	 *
	 * @param string $amazon_id Amazon product id.
	 * @param string $field_name  Field name.
	 * @param string $field_value Field value.
	 */
	public function update_amazon_field( $amazon_id, $field_name, $field_value ) {
		$sql     = '
			update ' . $this->get_table_name() . '
			set `' . $field_name . '` = %s
			where amazon_id = %s		
		';
		$prepare = Model::prepare( $sql, $field_value, $amazon_id ); // phpcs:ignore

		return Model::query( $prepare ); // phpcs:ignore
	}

	/**
	 * Whether any published Lite link flagged a customer price override for this ASIN.
	 *
	 * @param string $amazon_id Amazon product id.
	 */
	public static function product_has_customer_price_override( $amazon_id ) {
		return self::product_has_customer_override_meta( $amazon_id, Meta_Enum::CUSTOMER_PRICE_OVERRIDE );
	}

	/**
	 * Whether any published Lite link flagged a customer image override for this ASIN.
	 *
	 * @param string $amazon_id Amazon product id.
	 */
	public static function product_has_customer_image_override( $amazon_id ) {
		return self::product_has_customer_override_meta( $amazon_id, Meta_Enum::CUSTOMER_IMAGE_OVERRIDE );
	}

	/**
	 * Whether a linked Lite post has the given customer override meta flag.
	 *
	 * @param string $amazon_id Amazon product id.
	 * @param string $meta_key  Post meta key for the override flag.
	 */
	private static function product_has_customer_override_meta( $amazon_id, $meta_key ) {
		if ( '' === (string) $amazon_id ) {
			return false;
		}

		$url_details = new Url_Details();
		$posts       = Model::get_wp_table_name( 'posts' );
		$postmeta    = Model::get_wp_table_name( 'postmeta' );
		$sql         = '
			SELECT 1
			FROM ' . $url_details->get_table_name() . ' AS ud
				INNER JOIN ' . $posts . ' AS p ON ud.lasso_id = p.ID
				INNER JOIN ' . $postmeta . ' AS pm ON pm.post_id = p.ID
			WHERE ud.product_id = %s
				AND ud.product_type = %s
				AND p.post_type = %s
				AND p.post_status = %s
				AND pm.meta_key = %s
				AND pm.meta_value = %s
			LIMIT 1
		';
		$prepare     = Model::prepare(
			$sql,
			$amazon_id,
			Amazon_Api::PRODUCT_TYPE,
			Constant::LASSO_POST_TYPE,
			'publish',
			$meta_key,
			'1'
		);

		return (bool) Model::get_var( $prepare );
	}

	/**
	 * Persist free Marketplace image/price from the eligible BLS path.
	 *
	 * @param array       $product    Product fields for Amazon_Api::update_amazon_product_in_db().
	 * @param bool|string $updated_at Optional updated timestamp.
	 * @return bool
	 */
	public static function store_marketplace_free_product( $product, $updated_at = false ) {
		$api = new Amazon_Api();

		return (bool) $api->update_amazon_product_in_db( $product, $updated_at, true );
	}
}
