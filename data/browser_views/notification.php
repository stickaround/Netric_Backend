<?php
/**
 * Return browser views for entity of object type 'notification'
 */

namespace data\browser_views;

use Netric\Entity\ObjType\UserEntity;
use Netric\EntityQuery\Where;

return array(
    'my_notifications' => array(
        'obj_type' => 'notification',
        'name' => 'My Notifications',
        'description' => 'Display all my reminders',
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
        'table_columns' => array('name', 'ts_execute')
    ),
);
