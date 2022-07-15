<?php
/**
 * Return browser views for entity of object type 'html_template'
 */

namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\ObjectTypes;

return [
    'obj_type' => ObjectTypes::HTML_TEMPLATE,
    'views' => [
        'all_html_templates' => [
            'name' => 'All HTML Templates',
            'description' => 'Display all available HTML templates for all object types',
            'default' => true,
            'filter_fields' => ['obj_type'],
            'order_by' => [
                'obj_type' => [
                    'field_name' => 'obj_type',
                    'direction' => 'asc',
                ],
                'name' => [
                    'field_name' => 'name',
                    'direction' => 'asc',
                ],
            ],
            'table_columns' => ['name', 'obj_type']
        ],
    
        'email_templates' => [            
            'name' => 'Email Templates',
            'description' => 'HTML templates designed specifically for email messages',
            'default' => false,
            'filter_fields' => [],
            'conditions' => [
                'obj_type' => [
                    'blogic' => Where::COMBINED_BY_AND,
                    'field_name' => 'obj_type',
                    'operator' => Where::OPERATOR_EQUAL_TO,
                    'value' => 'email_message'
                ],
            ],
            'order_by' => [
                'name' => [
                    'field_name' => 'name',
                    'direction' => 'asc',
                ],
            ],
            'table_columns' => ['name']
        ],
    
        'my_templates' => [            
            'name' => 'My Templates',
            'description' => 'HTML templates designed by me',
            'default' => false,
            'filter_fields' => ['obj_type'],
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
            'table_columns' => ['name', 'obj_type']
        ],
    ]
];
