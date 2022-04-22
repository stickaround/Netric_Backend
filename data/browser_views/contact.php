<?php

/**
 * Return browser views for entity of object type 'contact'
 */

namespace data\browser_views;

use Netric\Entity\ObjType\ContactEntity;
use Netric\EntityQuery\Where;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\ObjectTypes;

return [
    'allactive' => [
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
        'conditions' => [
            'stage_id' => [
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'stage_id',
                'operator' => Where::OPERATOR_NOT_EQUAL_TO,
                'value' => ContactEntity::STAGE_INACTIVE,
            ],
        ],
        'table_columns' => [
            'name', 'email', 'stage_id', 'status_id', 'owner_id', 'city', 'district'
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
            'name', 'email', 'stage_id', 'status_id', 'owner_id', 'city', 'district'
        ]
    ],

    'leads' => [
        'obj_type' => ObjectTypes::CONTACT,
        'name' => 'Leads',
        'description' => 'Leads are contacts that are being vetted for the possibility of becoming a prospect or customer.',
        'default' => false,
        'conditions' => [
            'stage_id' => [
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'stage_id',
                'operator' => Where::OPERATOR_EQUAL_TO,
                'value' => ContactEntity::STAGE_LEAD,
            ],
        ],
        'order_by' => [
            'name' => [
                'field_name' => 'name',
                'direction' => 'asc',
            ],
        ],
        'table_columns' => [
            'name', 'email', 'stage_id', 'status_id', 'owner_id', 'city', 'district', 'country'
        ]
    ],

    'prospects' => [
        'obj_type' => ObjectTypes::CONTACT,
        'name' => 'Prospects',
        'description' => 'Prospects are qualified leads that have a good chance of coverting to a paying customer.',
        'default' => false,
        'conditions' => [
            'stage_id' => [
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'stage_id',
                'operator' => Where::OPERATOR_EQUAL_TO,
                'value' => ContactEntity::STAGE_PROSPECT,
            ],
        ],
        'order_by' => [
            'name' => [
                'field_name' => 'name',
                'direction' => 'asc',
            ],
        ],
        'table_columns' => [
            'name', 'email', 'stage_id', 'status_id', 'owner_id', 'city', 'district', 'country'
        ]
    ],

    'customers' => [
        'obj_type' => ObjectTypes::CONTACT,
        'name' => 'Customers',
        'description' => 'Customers are people who you do business with - often involving a financial transaction',
        'default' => false,
        'conditions' => [
            'stage_id' => [
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'stage_id',
                'operator' => Where::OPERATOR_EQUAL_TO,
                'value' => ContactEntity::STAGE_CUSTOMER,
            ],
        ],
        'order_by' => [
            'name' => [
                'field_name' => 'name',
                'direction' => 'asc',
            ],
        ],
        'table_columns' => [
            'name', 'email', 'stage_id', 'status_id', 'owner_id', 'city', 'district', 'country'
        ]
    ],

    'inactive' => [
        'obj_type' => ObjectTypes::CONTACT,
        'name' => 'Inactive Contacts',
        'description' => 'Contacts that have been archived due to inactivity - no longer a lead, customer, or prospect',
        'default' => false,
        'conditions' => [
            'stage_id' => [
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'stage_id',
                'operator' => Where::OPERATOR_EQUAL_TO,
                'value' => ContactEntity::STAGE_INACTIVE,
            ],
        ],
        'order_by' => [
            'name' => [
                'field_name' => 'name',
                'direction' => 'asc',
            ],
        ],
        'table_columns' => [
            'name', 'email', 'stage_id', 'status_id', 'owner_id', 'city', 'district', 'country'
        ]
    ],
];
