<?php
/**
 * Return browser views for entity of object type 'invoice'
 */
namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
    'obj_type' => ObjectTypes::INVOICE,
    'filter_fields' => [],
    'views' => [
			'invoices'=> [		
				'name' => 'Invoices',
				'description' => 'Events occurring in the future',
				'default' => true,
				'filter_fields' => ['status_id', 'created_by'],
				'order_by' => [
					'date' => [
							'field_name' => 'date_due',
							'direction' => 'desc',
						],
				],
				'table_columns' => ['name', 'status_id', 'created_by', 'ts_entered', 'amount', 'date_due']
    ],
		]
];
