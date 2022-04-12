<?php

/**
 * Return browser views for entity of object type 'document'
 */

namespace data\browser_views;

use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityQuery\Where;

return [
	'all_documents' => [
		'obj_type' => ObjectTypes::DOCUMENT,
		'name' => 'All Documents',
		'description' => 'All InfoCenter Documents',
		'default' => true,
		'order_by' => [
			'title' => [
				'field_name' => 'title',
				'direction' => 'asc',
			],
		],
		'table_columns' => ['title', 'keywords', 'ts_updated', 'owner_id']
	],
	'spaces' => [
		'obj_type' => ObjectTypes::DOCUMENT,
		'name' => 'Spaces',
		'description' => 'Infocenter documents that will function more like a wiki.',
		'default' => false,
		'conditions' => [
			'is_rootspace' => [
				'blogic' => Where::COMBINED_BY_AND,
				'field_name' => 'is_rootspace',
				'operator' => Where::OPERATOR_EQUAL_TO,
				'value' => true,
			]
		],
		'order_by' => [
			'title' => [
				'field_name' => 'title',
				'direction' => 'asc',
			],
		],
		'table_columns' => ['title', 'keywords', 'ts_updated', 'owner_id']
	],
];