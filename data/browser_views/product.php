<?php
/**
 * Return browser views for entity of object type 'product'
 */
namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
    "obj_type" => ObjectTypes::PRODUCT,
    "views" => [
				'default'=> [
					'name' => 'Default View',
					'description' => 'All Products',
					'default' => true,
					'order_by' => [
						'name' => [
								'field_name' => 'name',
								'direction' => 'desc',
							],
					],
					'table_columns' => ['name', 'price', 'notes', 'ts_updated', 'ts_entered']
				],
		]
];
