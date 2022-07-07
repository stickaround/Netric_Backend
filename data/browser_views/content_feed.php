<?php
/**
 * Return browser views for entity of object type 'content_feed'
 */
namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
		"obj_type" => ObjectTypes::CONTENT_FEED,
		"filters" => [],
    "views" => [
				'all_feeds'=> [
					'name' => 'All Content Feeds',
					'description' => 'All Content Feeds',
					'default' => true,
					'order_by' => [
						'date' => [
								'field_name' => 'ts_updated',
								'direction' => 'desc',
							],
					],
					'table_columns' => ['title', 'ts_updated']
			],
		]
];
