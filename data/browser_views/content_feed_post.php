<?php
/**
 * Return browser views for entity of object type 'content_feed_post'
 */
namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
		"obj_type" => ObjectTypes::CONTENT_FEED_POST,
		"filters" => [],
    "views" => [
			'all_posts'=> [		
				'name' => 'All Posts',
				'description' => 'All Content Feed Posts',
				'default' => true,
				'order_by' => [
					'date' => [
							'field_name' => 'time_entered',
							'direction' => 'desc',
						],
				],
				'table_columns' => ['title', 'status_id', 'owner_id', 'time_entered', 'ts_updated']
				],
				
			'drafts'=> [		
				'name' => 'Drafts',
				'description' => 'Drafts',
				'default' => false,
				'conditions' => [
					'not_publish' => [
						'blogic' => Where::COMBINED_BY_AND,
						'field_name' => 'f_publish',
						'operator' => Where::OPERATOR_NOT_EQUAL_TO,
						'value' => 't'
					],
				],
				'order_by' => [
					'date' => [
							'field_name' => 'time_entered',
							'direction' => 'desc',
					],
				],
				'table_columns' => ['title', 'owner_id', 'time_entered', 'ts_updated']
			],
				
			'published'=> [		
				'name' => 'Published',
				'description' => 'All published posts',
				'default' => false,
				'conditions' => [
					'not_publish' => [
						'blogic' => Where::COMBINED_BY_AND,
						'field_name' => 'f_publish',
						'operator' => Where::OPERATOR_EQUAL_TO,
						'value' => 't'
					],
				],
				'order_by' => [
					'date' => [
						'field_name' => 'time_entered',
						'direction' => 'desc',
					],
				],
				'table_columns' => ['title', 'owner_id', 'time_entered', 'ts_updated']
			],
		]
];
