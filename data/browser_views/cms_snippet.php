<?php

/**
 * Return browser views for entity of object type 'cms_snippet'
 */

namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
	"obj_type" => ObjectTypes::HTML_SNIPPET,
	"filters" => [],
	"views" => [
		'all_snippets' => [
			'name' => 'All Snippets',
			'description' => 'All Snippets',
			'default' => true,
			'order_by' => [
				'name' => [
					'field_name' => 'name',
					'direction' => 'desc',
				],
			],
			'table_columns' => ['name']
		],
	]
];
