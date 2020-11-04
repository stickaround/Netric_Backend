<?php
/**
 * Return browser views for entity of object type 'lead'
 */

namespace data\browser_views;

use Netric\Entity\ObjType\UserEntity;
use Netric\EntityQuery\Where;

return array(
    'my_leads' => array(
        'obj_type' => 'lead',
        'name' => 'My Leads',
        'description' => 'Leads Assigned To Me',
        'default' => true,
        'conditions' => array(
            'owner' => array(
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'owner_id',
                'operator' => Where::OPERATOR_EQUAL_TO,
                'value' => UserEntity::USER_CURRENT,
            ),
        ),
        'order_by' => array(
            'sort_order' => array(
                'field_name' => 'sort_order',
                'direction' => 'desc',
            ),
        ),
        'table_columns' => array('first_name', 'last_name', 'email', 'phone', 'city', 'state', 'status_id', 'rating_id', 'ts_entered')
    ),

    'all_leads' => array(
        'obj_type' => 'lead',
        'name' => 'All Leads',
        'description' => 'All Leads',
        'default' => false,
        'conditions' => array(
            'owner' => array(
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'owner_id',
                'operator' => Where::OPERATOR_EQUAL_TO,
                'value' => UserEntity::USER_CURRENT,
            ),
        ),
        'order_by' => array(
            'sort_order' => array(
                'field_name' => 'sort_order',
                'direction' => 'desc',
            ),
        ),
        'table_columns' => array('first_name', 'last_name', 'email', 'phone', 'city', 'state', 'status_id', 'rating_id', 'ts_entered')
    ),
);
