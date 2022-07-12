<?php

/**
 * Return browser views for entity of object type 'document'
 */

namespace data\browser_views;

use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityQuery\Where;

return [
	'obj_type' => ObjectTypes::DOCUMENT,
	'views' => [
			'all_documents' => [		
				'name' => 'All Documents',
				'description' => 'All InfoCenter Documents',
				'default' => true,
				'filter_fields' => ['owner_id', 'keywords'],
				'order_by' => [
					'title' => [
						'field_name' => 'title',
						'direction' => 'asc',
					],
				],
				'table_columns' => ['title', 'keywords', 'ts_updated', 'owner_id']
			],
			'spaces' => [
				'name' => 'Spaces',
				'description' => 'Infocenter documents that will function more like a wiki.',
				'default' => false,
				'filter_fields' => ['owner_id', 'keywords'],
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
	]
];
