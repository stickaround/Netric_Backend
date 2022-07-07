<?php

/**
 * Return browser views for entity of object type 'contact_personal'
 */

namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\ObjectTypes;

return [
    "obj_type" => ObjectTypes::CONTACT_PERSONAL,
    "filters" => [],
    "views" => [
        'my_contacts' => [
            'name' => 'My Contacts',
            'description' => 'User Contacts',
            'default' => true,
            'conditions' => [
                'user' => [
                    'blogic' => Where::COMBINED_BY_AND,
                    'field_name' => 'owner_id',
                    'operator' => Where::OPERATOR_EQUAL_TO,
                    'value' => UserEntity::USER_CURRENT,
                ],
            ],
            'order_by' => [
                'first_name' => [
                    'field_name' => 'first_name',
                    'direction' => 'asc',
                ],
                'last_name' => [
                    'field_name' => 'last_name',
                    'direction' => 'asc',
                ],
            ],
            'table_columns' => ['name', 'phone_cell', 'phone_home', 'phone_work', 'email_default', 'city', 'district']
        ],
    ]
];
