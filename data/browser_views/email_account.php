<?php
/**
 * Return browser views for entity of object type 'email_account'
 */
namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
    "obj_type" => ObjectTypes::EMAIL_ACCOUNT,
	"filters" => [],
    "views" => [
        'all_email_accounts'=> [        
            'name' => 'All Email Accounts',
            'description' => 'Display all email accounts',
            'default' => true,
            'order_by' => [
                'date' => [
                    'field_name' => 'name',
                    'direction' => 'asc',
                ],
            ],
            'table_columns' => ['name', "address", 'reply_to', 'f_default']
        ],
    ]
];
