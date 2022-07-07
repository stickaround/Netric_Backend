<?php
/**
 * Return browser views for entity of object type 'calendar'
 */
namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
		"obj_type" => ObjectTypes::CALENDAR,
    "filters" => [],
    "views" => [
			'all_calendars'=> [		
				'name' => 'All Calendars',
				'description' => 'Viewing All Calendars',
				'default' => true,
				'public' => [
								'user' => [
										'blogic' => Where::COMBINED_BY_AND,
										'field_name' => 'f_public',
										'operator' => Where::OPERATOR_EQUAL_TO,
										'value' => 't'
								],
						],
				'order_by' => [
					'date' => [
							'field_name' => 'name',
							'direction' => 'asc',
						],
				],
				'table_columns' => ['name', 'owner_id', 'f_view']
				],
		]
];
