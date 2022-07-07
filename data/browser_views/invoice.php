<?php
/**
 * Return browser views for entity of object type 'invoice'
 */
namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
    "obj_type" => ObjectTypes::INVOICE,
    "filters" => [],
    "views" => [
			'invoices'=> [		
				'name' => 'Invoices',
				'description' => 'Events occurring in the future',
				'default' => true,
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
