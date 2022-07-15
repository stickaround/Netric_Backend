<?php
/**
 * Return browser views for entity of object type 'product_family'
 */
namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
    'obj_type' => ObjectTypes::PRODUCT_FAMILY,
    'views' => [
				'default'=> [
					'name' => 'Default View: All Product Families',
					'description' => '',
					'default' => true,
					'filter_fields' => [],
					'order_by' => [
						'date' => [
								'field_name' => 'name',
								'direction' => 'desc',
							],
					],
					'table_columns' => ['name', 'ts_updated', 'ts_entered']
				],
		]
];
