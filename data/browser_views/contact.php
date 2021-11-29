<?php

/**
 * Return browser views for entity of object type 'contact'
 */

namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\ObjectTypes;

return [
    'all-contacts' => [
        'obj_type' => ObjectTypes::CONTACT,
        'name' => 'All Contacts',
        'description' => 'Default System View',
        'default' => true,
        'order_by' => [
            'name' => [
                'field_name' => 'name',
                'direction' => 'asc',
            ],
        ],
        'table_columns' => [
            'name', 'email_default', 'stage_id', 'status_id', 'owner_id', 'city', 'state'
        ]
    ],

    'my' => [
        'obj_type' => ObjectTypes::CONTACT,
        'name' => 'Assigned to me',
        'description' => 'Default System View',
        'default' => false,
        'conditions' => [
            'owner' => [
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'owner_id',
                'operator' => Where::OPERATOR_EQUAL_TO,
                'value' => UserEntity::USER_CURRENT,
            ],
        ],
        'order_by' => [
            'name' => [
                'field_name' => 'name',
                'direction' => 'asc',
            ],
        ],
        'table_columns' => [
            'name', 'email_default', 'stage_id', 'status_id', 'owner_id', 'city', 'state'
        ]
    ],
];
