<?php
/**
 * Return browser views for entity of object type 'payment_profile'
 */
namespace data\browser_views;

use Netric\EntityQuery\Where;

return array(
    'all_payment_profiles'=> array(
        'obj_type' => 'payment_profile',
        'name' => 'All Payment Profiles',
        'description' => 'All Payment Profiles',
        'default' => true,
        'order_by' => array(
            'f_default' => array(
                'field_name' => 'f_default',
                'direction' => 'desc',
            ),
            'name' => array(
                'field_name' => 'name',
                'direction' => 'asc',
            ),
        ),
        'table_columns' => array('name', 'f_default', 'payment_gateway')
    ),
);
