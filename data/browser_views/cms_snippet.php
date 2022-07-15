<?php

/**
 * Return browser views for entity of object type 'cms_snippet'
 */

namespace data\browser_views;

use Netric\EntityDefinition\ObjectTypes;

return [
	'obj_type' => ObjectTypes::HTML_SNIPPET,
	'views' => [
		'all_snippets' => [
			'name' => 'All Snippets',
			'description' => 'All Snippets',
			'default' => true,
			'filter_fields' => [],
			'order_by' => [
				'name' => [
					'field_name' => 'name',
					'direction' => 'desc',
				],
			],
		],
		'table_columns' => ['name']
	],
];
