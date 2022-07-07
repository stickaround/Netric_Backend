<?php
/**
 * Return browser views for entity of object type 'sales_order'
 */
namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
    "obj_type" => ObjectTypes::SALES_ORDER,
		"filters" => [],
    "views" => [
				'all_orders'=> [
					'name' => 'All Orders',
					'description' => 'All Orders',
					'default' => true,
					'order_by' => [
						'sort_order' => [
								'field_name' => 'sort_order',
								'direction' => 'desc',
							],
					],
					'table_columns' => ['name', 'status_id', 'created_by', 'ts_entered', 'amount', 'customer_id']
				],
		]
];
