<?php

namespace data\entity_definitions;

use Netric\EntityDefinition\Field;
use Netric\EntityDefinition\ObjectTypes;

return [
    'is_private' => true,
    'default_activity_level' => 1,
    'store_revisions' => true,
    'fields' => [
        // Textual name of the account
        'name' => [
            'title' => 'Title',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false,
            'require' => true
        ],

        "type" => [
            'title' => 'Server Type',
            'type' => Field::TYPE_TEXT,
            'subtype' => '4',
            'readonly' => false,
            'optional_values' => [
                "none" => "None - just reply from this address",
                "imap" => "IMAP",
                "pop3" => "POP3",
                "dropbox" => "System Dropbox"
            ],
        ],

        'address' => [
            'title' => 'Email Address',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false,
            'require' => true
        ],

        'reply_to' => [
            'title' => 'Reply To',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false
        ],

        'signature' => [
            'title' => 'Signature',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false
        ],

        'host' => [
            'title' => 'Host',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false,
            'require' => true
        ],

        'username' => [
            'title' => 'Username',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false,
            'require' => true
        ],

        'password' => [
            'title' => 'Password',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false,
            'require' => true
        ],

        'port' => [
            'title' => 'Port',
            'type' => Field::TYPE_NUMBER,
            'subtype' => 'integer',
            'readonly' => false,
            'require' => true
        ],

        'f_default' => [
            'title' => 'Default Account',
            'type' => Field::TYPE_BOOL,
            'readonly' => false
        ],

        'f_ssl' => [
            'title' => 'Require SSL',
            'type' => Field::TYPE_BOOL,
            'readonly' => false
        ],

        'sync_data' => [
            'title' => 'Sync Data',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false
        ],

        'ts_last_full_sync' => [
            'title' => 'Last Full Sync',
            'type' => Field::TYPE_TIMESTAMP,
            'subtype' => '',
            'readonly' => false
        ],

        'f_synchronizing' => [
            'title' => 'Sync In Process',
            'type' => Field::TYPE_BOOL,
            'subtype' => 'true'
        ],

        'f_system' => [
            'title' => 'System',
            'type' => Field::TYPE_BOOL,
            'readonly' => false
        ],

        'f_outgoing_auth' => [
            'title' => 'Outgoing Auth',
            'type' => Field::TYPE_BOOL,
            'readonly' => false
        ],

        'host_out' => [
            'title' => 'Host Out',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false
        ],

        'port_out' => [
            'title' => 'Port Out',
            'type' => Field::TYPE_NUMBER,
            'subtype' => 'integer',
            'readonly' => false
        ],

        'f_ssl_out' => [
            'title' => 'SSL Out',
            'type' => Field::TYPE_BOOL,
            'readonly' => false
        ],

        'username_out' => [
            'title' => 'Username Out',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false
        ],

        'password_out' => [
            'title' => 'Password Out',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false
        ],

        'forward' => [
            'title' => 'Forward',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false
        ],

        // Used for type=dropbox
        'dropbox_create_type' => [
            'title' => "Create Type",
            'type' => Field::TYPE_TEXT,
            'subtype' => '64',
            'optional_values' => [
                // Supported dropboxes
                ObjectTypes::TICKET => "Ticket",
                ObjectTypes::COMMENT => "Comment",
                // TODO: later we'll add note, file, activity
            ],
            'readonly' => true,
        ],

        'dropbox_obj_reference' => [
            'title' => 'References',
            'type' => Field::TYPE_OBJECT,
            'subtype' => '',
            'readonly' => true,
        ],
    ],
];
