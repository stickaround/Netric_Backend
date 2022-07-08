<?php
/**
 * Return browser views for entity of object type 'calendar_event_proposal'
 */
namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
		"obj_type" => ObjectTypes::CALENDAR_EVENT_PROPOSAL,
    "filters" => [],
    "views" => [
			'meeting_proposals'=> [		
				'name' => 'Meeting Proposals',
				'description' => 'Meeting proposals that are still in process',
				'default' => true,
				'conditions' => [
								'closed' => [
										'blogic' => Where::COMBINED_BY_AND,
										'field_name' => 'f_closed',
										'operator' => Where::OPERATOR_NOT_EQUAL_TO,
										'value' => 't'
								],
						],
				'order_by' => [
					'sort_order' => [
							'field_name' => 'sort_order',
							'direction' => 'desc',
						],
				],
				'table_columns' => ['name', 'location', 'status_id', 'ts_updated']
				],
				
			'closed_proposals'=> [		
				'name' => 'Closed Proposals',
				'description' => 'Meeting proposals that have been closed and/or converted to events.',
				'default' => false,
				'conditions' => [
					'closed' => [
							'blogic' => Where::COMBINED_BY_AND,
							'field_name' => 'f_closed',
							'operator' => Where::OPERATOR_EQUAL_TO,
							'value' => 't'
					],
				],
				'order_by' => [
					'sort_order' => [
							'field_name' => 'sort_order',
							'direction' => 'desc',
					],
				],
				'table_columns' => ['name', 'location', 'status_id', 'ts_updated']
			],
				
			'all_meeting_proposals'=> [		
				'name' => 'All Meeting Proposals',
				'description' => 'All Meeting Proposals',
				'default' => false,		
				'order_by' => [
					'sort_order' => [
							'field_name' => 'sort_order',
							'direction' => 'desc',
					],
				],
				'table_columns' => ['name', 'location', 'status_id', 'ts_updated']
			],
		]
];
