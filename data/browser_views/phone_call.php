<?php
/**
 * Return browser views for entity of object type 'phone_call'
 */
namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
    "obj_type" => ObjectTypes::PHONE_CALL,
    "filters" => [],
    "views" => [
				'all_pages'=> [		
					'name' => 'All Pages',
					'description' => 'Display all pages',
					'default' => true,
					'order_by' => [
						'date' => [
								'field_name' => 'name',
								'direction' => 'desc',
							],
					],
					'table_columns' => ['name', 'uname', 'title', 'parent_id']
				],
		]
];
