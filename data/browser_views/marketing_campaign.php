<?php
/**
 * Return browser views for entity of object type 'marketing_campaign'
 */
namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
    "obj_type" => ObjectTypes::MARKETING_CAMPAIGN,
    "filters" => [],
    "views" => [
			'all_campaigns'=> [
				'name' => 'All Campaigns',
				'description' => 'View all campaigns both active and inactive',
				'default' => true,
				'order_by' => [
					'date' => [
							'field_name' => 'date_start',
							'direction' => 'desc',
						],
				],
				'table_columns' => ['name', 'status_id', 'type_id', 'date_start']
    ],
		]
];
