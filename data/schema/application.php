<?php

/**
         * Schema file for the netric application database
     */

namespace data\schema;

use Netric\Application\Schema\SchemaProperty;

return [
    /**
     * Main accounts table/bucket
     */
    "accounts" => [
        "PROPERTIES" => [
            'id' => ['type' => SchemaProperty::TYPE_BIGSERIAL],
            'name' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'database' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'ts_started' => ['type' => SchemaProperty::TYPE_TIMESTAMP],
            'server' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'version' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'active' => ['type' => SchemaProperty::TYPE_BOOL, 'default' => 't'],
        ],
        'PRIMARY_KEY'       => 'id',
        // TODO: constraints for unique name
        "INDEXES" => [
            ['properties' => ["name"]],
            ['properties' => ["version"]],
        ]
    ],

    /**
     * Index of users is synchronized with the specific user in the account
     * schema. We use this to very cross-reference users by guid, email, and username.
     */
    "account_users" => [
        "PROPERTIES" => [
            'id' => ['type' => SchemaProperty::TYPE_BIGSERIAL],
            'guid' => ['type' => SchemaProperty::TYPE_UUID],
            'account_id' => ['type' => SchemaProperty::TYPE_BIGINT],
            'email_address' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'username' => ['type' => SchemaProperty::TYPE_CHAR_256],
        ],
        'PRIMARY_KEY'       => 'id',
        // TODO: constraints for unique account_id, email_alias
        "KEYS" => [
            [
                "property" => 'account_id',
                'references_bucket' => 'accounts',
                'references_property' => 'id',
                'on_delete' => 'cascade',
                'on_update' => 'cascade',
            ]
        ],
        "INDEXES" => [
            ['properties' => ["email_address"]],
        ]
    ],

    "email_alias" => [
        "PROPERTIES" => [
            'address' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'goto' => ['type' => SchemaProperty::TYPE_CHAR_TEXT],
            'active' => ['type' => SchemaProperty::TYPE_BOOL, 'default' => 't'],
            'account_id' => ['type' => SchemaProperty::TYPE_BIGINT],
        ],
        'PRIMARY_KEY' => 'address',
        "KEYS" => [
            [
                "property" => 'account_id',
                'references_bucket' => 'accounts',
                'references_property' => 'id',
                'on_delete' => 'cascade',
                'on_update' => 'cascade',
            ]
        ],
    ],
    "email_domains" => [
        "PROPERTIES" => [
            'domain' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'description' => ['type' => SchemaProperty::TYPE_CHAR_TEXT],
            'active' => ['type' => SchemaProperty::TYPE_BOOL, 'default' => 't'],
            'account_id' => ['type' => SchemaProperty::TYPE_BIGINT],
        ],
        'PRIMARY_KEY' => 'domain',
        "KEYS" => [
            [
                "property" => 'account_id',
                'references_bucket' => 'accounts',
                'references_property' => 'id',
                'on_delete' => 'cascade',
                'on_update' => 'cascade',
            ]
        ],
    ],
    "email_users" => [
        "PROPERTIES" => [
            'id' => ['type' => SchemaProperty::TYPE_BIGSERIAL],
            'email_address' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'maildir' => ['type' => SchemaProperty::TYPE_CHAR_128],
            'password' => ['type' => SchemaProperty::TYPE_CHAR_128],
            'account_id' => ['type' => SchemaProperty::TYPE_BIGINT],
        ],
        'PRIMARY_KEY' => 'id',
        "KEYS" => [
            [
                "property" => 'account_id',
                'references_bucket' => 'accounts',
                'references_property' => 'id',
                'on_delete' => 'cascade',
                'on_update' => 'cascade',
            ]
        ],
    ],
    "settings" => [
        "PROPERTIES" => [
            'id' => ['type' => SchemaProperty::TYPE_BIGSERIAL],
            'name' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'value' => ['type' => SchemaProperty::TYPE_CHAR_TEXT]
        ],
        'PRIMARY_KEY' => 'name',
    ],
    "worker_process_lock" => [
        "PROPERTIES" => [
            'id' => ['type' => SchemaProperty::TYPE_BIGSERIAL],
            'process_name' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'ts_entered' => ['type' => SchemaProperty::TYPE_TIMESTAMP],
        ],
        'PRIMARY_KEY' => 'id',
        "INDEXES" => [
            ['properties' => ["ts_entered"]],
            ['properties' => ["process_name"], 'type' => 'UNIQUE'],
        ],
    ],
];
