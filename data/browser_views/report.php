<?php
/**
 * Return browser views for entity of object type 'report'
 */
namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
    'obj_type' => ObjectTypes::REPORT,
    'views' => [
				'reports'=> [
					'name' => 'Reports',
					'description' => 'Default list of reports',
					'default' => true,
					'filter_fields' => [],
					'order_by' => [
						'name' => [
								'field_name' => 'name',
								'direction' => 'asc',
							],
					],
					'table_columns' => ['name', 'description']
				],
		]
];
