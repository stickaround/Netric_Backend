<?php
/**
 * Return browser views for entity of object type 'member'
 */
namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
    "obj_type" => ObjectTypes::MEMBER,
    "filters" => [],
    "views" => [
			'all_members'=> [
				'name' => 'All Members',
				'description' => '',
				'default' => true,
				'order_by' => [
					'sort_order' => [
							'field_name' => 'sort_order',
							'direction' => 'desc',
						],
				],
				'table_columns' => ['name', 'role', 'f_accepted', 'ts_entered', 'obj_reference']
    ],
		]
];
