<?php
/**
 * Return browser views for entity of object type 'email_campaign'
 */
namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
		'obj_type' => ObjectTypes::EMAIL_CAMPAIGN,
    'views' => [
			'all_email_campaigns'=> [		
				'name' => 'All Email Campaigns',
				'description' => 'Display all available HTML templates for all object types',
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
