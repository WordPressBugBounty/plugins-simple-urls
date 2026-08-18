<?php
/**
 * Declare class Verbiage
 *
 * @package Verbiage
 */

namespace LassoLite\Classes;

use LassoLite\Classes\Processes\Import_All;
use LassoLite\Classes\Processes\Revert_All;

/**
 * Verbiage
 */
abstract class Verbiage {
	const PROCESS_DESCRIPTION = array(
		Import_All::class => 'Importing links',
		Revert_All::class => 'Reverting links',
	);
}
