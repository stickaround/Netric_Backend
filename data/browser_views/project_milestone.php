<?php
/**
 * Return browser views for entity of object type 'project_milestone'
 */
namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
    "obj_type" => ObjectTypes::PROJECT_MILESTONE,
    "filters" => [],
    "views" => [
				'default'=> [
					'name' => 'Default View',
					'description' => 'All Milestones',
					'default' => true,
					'order_by' => [
						'deadline' => [
								'field_name' => 'deadline',
								'direction' => 'desc',
							],
					],
					'table_columns' => ['name', 'deadline', 'owner_id', 'project_id', 'f_completed']
				],
		]
];
