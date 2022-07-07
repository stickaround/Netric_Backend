<?php
/**
 * Return browser views for entity of object type 'calendar_event'
 */
namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
	"obj_type" => ObjectTypes::CALENDAR_EVENT,
    "filters" => [],
    "views" => [
			'upcoming_events'=> [		
				'name' => 'Upcoming Events',
				'description' => 'Events occurring in the future',
				'default' => true,
				'conditions' => [
								'start' => [
										'blogic' => Where::COMBINED_BY_AND,
										'field_name' => 'ts_start',
										'operator' => Where::OPERATOR_LESS_THAN_OR_EQUAL_TO,
										'value' => 'now'
								],
						],
				'order_by' => [
					'date' => [
							'field_name' => 'ts_start',
							'direction' => 'desc',
						],
				],
				'table_columns' => ['name', 'location', 'ts_start', 'ts_end', 'owner_id']
				],
				
			'my_past_events'=> [		
				'name' => 'My Past Events',
				'description' => 'Events that occurred in the past',
				'default' => false,
				'conditions' => [
					'start' => [
						'blogic' => Where::COMBINED_BY_AND,
						'field_name' => 'ts_start',
						'operator' => Where::OPERATOR_LESS_THAN,
						'value' => 'now'
					],
				],
				'order_by' => [
					'date' => [
						'field_name' => 'ts_start',
						'direction' => 'desc',
					],
				],
				'table_columns' => ['name', 'location', 'ts_start', 'ts_end', 'owner_id']
			],
				
			'all_events'=> [		
				'name' => 'All Events',
				'description' => 'All Events',
				'default' => false,
				'order_by' => [
					'date' => [
						'field_name' => 'ts_start',
						'direction' => 'desc',
					],
				],
				'table_columns' => ['name', 'location', 'ts_start', 'ts_end', 'owner_id']
			],
		]
];
