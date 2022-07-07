<?php
/**
 * Return browser views for entity of object type 'notification'
 */

namespace data\browser_views;

use Netric\Entity\ObjType\UserEntity;
use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
    "obj_type" => ObjectTypes::NOTIFICATION,
    "filters" => [],
    "views" => [
        'my_notifications' => [
            'name' => 'My Notifications',
            'description' => 'Display all my reminders',
            'default' => true,
            'conditions' => [
                'owner' => [
                    'blogic' => Where::COMBINED_BY_AND,
                    'field_name' => 'owner_id',
                    'operator' => Where::OPERATOR_EQUAL_TO,
                    'value' => UserEntity::USER_CURRENT,
                ],
            ],
            'order_by' => [
                'date' => [
                    'field_name' => 'ts_entered',
                    'direction' => 'desc',
                ],
            ],
            'table_columns' => ['name', 'ts_execute']
        ],
    ]
];
