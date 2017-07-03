<?php
/**
 * Part of the Fuel framework.
 * 
 * - KienNH, 2016/02/29
 *     Copy from fuel/core/config/format.php
 *     Fix bug encode json return error JSON_ERROR_INF_OR_NAN by adding option JSON_PARTIAL_OUTPUT_ON_ERROR
 *
 * @package    Fuel
 * @version    1.7
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

return array(
	'csv' => array(
		'import' => array(
			'delimiter' => ',',
			'enclosure' => '"',
			'newline'   => "\n",
			'escape'    => '\\',
		),
		'export' => array(
			'delimiter' => ',',
			'enclosure' => '"',
			'newline'   => "\n",
			'escape'    => '\\',
		),
		'regex_newline'   => "\n",
		'enclose_numbers' => true,
	),
	'xml' => array(
		'basenode' => 'xml',
		'use_cdata' => false,
	),
	'json' => array(
		'encode' => array(
			'options' => JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_PARTIAL_OUTPUT_ON_ERROR,
		)
	)
);
