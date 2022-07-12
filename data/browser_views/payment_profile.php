<?php
/**
 * Return browser views for entity of object type 'payment_profile'
 */
namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
    'obj_type' => ObjectTypes::SALES_PAYMENT_PROFILE,
    'views' => [
        'all_payment_profiles'=> [        
            'name' => 'All Payment Profiles',
            'description' => 'All Payment Profiles',
            'default' => true,
            'filter_fields' => ['f_default', 'payment_gateway'],
            'order_by' => [
                'f_default' => [
                    'field_name' => 'f_default',
                    'direction' => 'desc',
                ],
                'name' => [
                    'field_name' => 'name',
                    'direction' => 'asc',
                ],
            ],
            'table_columns' => ['name', 'f_default', 'payment_gateway']
        ],
    ]
];
