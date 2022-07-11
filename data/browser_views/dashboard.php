<?php
/**
 * Return browser views for entity of object type 'dashboard'
 */
namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
		'obj_type' => ObjectTypes::DASHBOARD,
    'views' => [
				'all_dashboards'=> [
					'name' => 'All Dashboards',
					'description' => 'Viewing All Dashboards',
					'default' => true,
					'filter_fields' => [],
					'order_by' => [
						'name' => [
								'field_name' => 'name',
								'direction' => 'desc',
							],
					],
					'table_columns' => ['name', 'description']
    ],
		]
];
