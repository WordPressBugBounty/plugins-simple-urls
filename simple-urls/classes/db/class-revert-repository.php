<?php
/**
 * Revert table queries (extracted from Lasso_DB).
 *
 * @package Db
 */

namespace LassoLite\Classes\Db;

use LassoLite\Models\Model;
use LassoLite\Models\Revert;

/**
 * Revert_Repository
 */
class Revert_Repository {

	/**
	 * Get revert row by lasso post id.
	 *
	 * @param int $lasso_id Lasso post id.
	 */
	public function get_by_lasso_id( $lasso_id ) {
		$sql = '
			SELECT *
			FROM ' . ( new Revert() )->get_table_name() . '
			WHERE lasso_id = %d;
		';
		$sql = Model::prepare( $sql, $lasso_id );

		return Model::get_row( $sql );
	}
}
