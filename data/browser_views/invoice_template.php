<?php
/**
 * Return browser views for entity of object type 'invoice_template'
 */
namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
    'obj_type' => ObjectTypes::INVOICE_TEMPLATE,
    'views' => [
			'templates'=> [
				'name' => 'Templates',
				'description' => 'View All Invoice Templates',
				'default' => true,
				'filter_fields' => [],
				'order_by' => [
					'name' => [
							'field_name' => 'name',
							'direction' => 'desc',
						],
				],
				'table_columns' => ['name', 'company_name']
    ],
		]
];
