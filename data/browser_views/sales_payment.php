<?php
/**
 * Return browser views for entity of object type 'sales payment'
 */
namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
    'obj_type' => ObjectTypes::SALES_PAYMENT,
    'views' => [
        'all_payments'=> [
            'name' => 'All Payments',
            'description' => 'All Payments',
            'default' => true,
            'filter_fields' => ['owner_id'],
            'order_by' => [
                'date' => [
                    'field_name' => 'date_paid',
                    'direction' => 'desc',
                ],
            ],
            'table_columns' => ['date_paid', 'amount', 'owner_id']
        ],
    ]
];
