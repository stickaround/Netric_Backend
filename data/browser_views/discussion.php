<?php
/**
 * Return browser views for entity of object type 'discussion'
 */
namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
		"obj_type" => ObjectTypes::DISCUSSION,
		"filters" => [],
    "views" => [
			'discussions'=> [		
				'name' => 'Discussions',
				'description' => 'Discussions',
				'default' => true,
				'order_by' => [
					'sort_order' => [
							'field_name' => 'sort_order',
							'direction' => 'desc',
						],
				],
				'table_columns' => ['name', 'ts_updated', 'ts_entered', 'owner_id', 'obj_reference']
    ],
		]
];
