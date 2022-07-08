<?php
/**
 * Return browser views for entity of object type 'report'
 */
namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
    "obj_type" => ObjectTypes::REPORT,
    "filters" => [],
    "views" => [
				'reports'=> [
					'name' => 'Reports',
					'description' => 'Default list of reports',
					'default' => true,
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
