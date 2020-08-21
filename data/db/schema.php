<?php

namespace data\schema;

use Netric\Application\Schema\SchemaProperty;

/**
             * Schema file used for database
     */
return [
    /**
     * Main accounts table
     */
    "account" => [
        "PROPERTIES" => [
            'account_id' => ['type' => SchemaProperty::TYPE_UUID],
            'name' => ['type' => SchemaProperty::TYPE_CHAR_256, 'unique' => true],
            'database' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'ts_started' => ['type' => SchemaProperty::TYPE_TIMESTAMP],
            'server' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'version' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'active' => ['type' => SchemaProperty::TYPE_BOOL, 'default' => 't'],
            // UUID of the 'contact' in the main account used for billing and support
            'main_account_contact_id' => ['type' => SchemaProperty::TYPE_UUID],
            // The last time the account was successfully billed
            'billing_last_billed' => ['type' => SchemaProperty::TYPE_TIMESTAMP],
            // If true, the user will be forced to update billing details before continuing
            'billing_force_update' => ['type' => SchemaProperty::TYPE_BOOL],
        ],
        'PRIMARY_KEY' => 'account_id',
        // TODO: contraints for unique name
    ],

    /**
     * Index of users is synchronized with the specific user in the account
     * schema. We use this to very cross-reference users by guid, email, and username.
     */
    "account_user" => [
        "PROPERTIES" => [
            'account_id' => ['type' => SchemaProperty::TYPE_UUID, 'notnull' => true],
            'email_address' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'username' => ['type' => SchemaProperty::TYPE_CHAR_256],
        ],
        'PRIMARY_KEY' => ['account_id', 'email_address'],
        "INDEXES" => [
            ['properties' => ["email_address"]],
        ],
    ],

    /**
     * Used to lock a worker job so we can assure only one instance is running
     */
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

    //
    // Account Data Tables
    // -----------------------------------------------------------------------------------

    /**
     * Generic settings table used fro both user, account, and system settings
     */
    "settings" => [
        "PROPERTIES" => [
            'id' => ['type' => SchemaProperty::TYPE_BIGSERIAL],
            'name' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'value' => ['type' => SchemaProperty::TYPE_CHAR_TEXT],
            'user_id' => ['type' => SchemaProperty::TYPE_UUID],
            'account_id' => ['type' => SchemaProperty::TYPE_UUID],
        ],
        'PRIMARY_KEY' => 'id',
        "KEYS" => [
            ['properties' => ["name", "user_id"], 'type' => 'UNIQUE'],
        ],
        "INDEXES" => [
            ['properties' => ["user_id"]],
        ]
    ],

    /**
     * Forms the UI will use
     */
    "entity_form" => [
        "PROPERTIES" => [
            'entity_form_id' => ['type' => SchemaProperty::TYPE_UUID],
            'entity_definition_id' => ['type' => SchemaProperty::TYPE_UUID],
            'account_id' => ['type' => SchemaProperty::TYPE_UUID, 'notnull' => true],
            'team_id' => ['type' => SchemaProperty::TYPE_UUID],
            'user_id' => ['type' => SchemaProperty::TYPE_UUID],
            'scope' => ["type" => SchemaProperty::TYPE_CHAR_128],
            'form_layout_xml' => ["type" => SchemaProperty::TYPE_CHAR_TEXT],
        ],
        'PRIMARY_KEY' => 'entity_form_id',
        "INDEXES" => [
            ['properties' => ["entity_definition_id"]],
            ['properties' => ["team_id"]],
            ['properties' => ["user_id"]],
            ['properties' => ["scope"]],
        ]
    ],

    /**
     * Types of entities - entity defintion
     */
    "entity_definition" => [
        "PROPERTIES" => [
            'entity_definition_id' => ['type' => SchemaProperty::TYPE_UUID],
            'account_id' => ['type' => SchemaProperty::TYPE_UUID, 'notnull' => true],
            'name' => ["type" => SchemaProperty::TYPE_CHAR_256],
            'title' => ["type" => SchemaProperty::TYPE_CHAR_256],
            'revision' => ['type' => SchemaProperty::TYPE_INT, 'default' => '1'],
            'f_system' => ['type' => SchemaProperty::TYPE_BOOL, "default" => "false"],
            'system_definition_hash' => ['type' => SchemaProperty::TYPE_CHAR_32],
            'f_table_created' => ['type' => SchemaProperty::TYPE_BOOL, "default" => "false"],
            'application_id' => ['type' => SchemaProperty::TYPE_INT],
            'capped' => ['type' => SchemaProperty::TYPE_INT],
            'head_commit_id' => ["type" => "bigint"],
            'dacl' => ['type' => SchemaProperty::TYPE_CHAR_TEXT],
            'is_private' => ['type' => SchemaProperty::TYPE_BOOL, "default" => "false"],
            'store_revisions' => ['type' => SchemaProperty::TYPE_BOOL, "default" => "true"],
            'recur_rules' => ['type' => SchemaProperty::TYPE_CHAR_TEXT],
            'inherit_dacl_ref' => ["type" => SchemaProperty::TYPE_CHAR_128],
            'parent_field' => ["type" => SchemaProperty::TYPE_CHAR_128],
            'uname_settings' => ["type" => SchemaProperty::TYPE_CHAR_256],
            'list_title' => ["type" => SchemaProperty::TYPE_CHAR_128],
            'icon' => ["type" => SchemaProperty::TYPE_CHAR_128],
            'default_activity_level' => ['type' => SchemaProperty::TYPE_INT],
            'def_data' => ['type' => SchemaProperty::TYPE_JSON],
        ],
        'PRIMARY_KEY' => 'entity_definition_id',
        "INDEXES" => [
            ['properties' => ["name", "account_id"]],
        ]
    ],

    "entity_view" => [
        "PROPERTIES" => [
            'entity_view_id' => ['type' => SchemaProperty::TYPE_BIGSERIAL],
            'name' => ['type' => SchemaProperty::TYPE_CHAR_256, 'notnull' => true],
            'scope' => ['type' => SchemaProperty::TYPE_CHAR_16],
            'description' => ['type' => SchemaProperty::TYPE_CHAR_TEXT],
            'filter_key' => ['type' => SchemaProperty::TYPE_CHAR_TEXT],
            'f_default' => ['type' => SchemaProperty::TYPE_BOOL, "default" => "false"],
            'user_id' => ['type' => SchemaProperty::TYPE_UUID],
            'team_id' => ['type' => SchemaProperty::TYPE_UUID],
            'entity_definition_id' => ['type' => SchemaProperty::TYPE_UUID, 'notnull' => true],
            'report_id' => ['type' => SchemaProperty::TYPE_UUID],
            'owner_id' => ['type' => SchemaProperty::TYPE_UUID],
            'conditions_data' => ['type' => SchemaProperty::TYPE_CHAR_TEXT],
            'order_by_data' => ['type' => SchemaProperty::TYPE_CHAR_TEXT],
            'table_columns_data' => ['type' => SchemaProperty::TYPE_CHAR_TEXT],
            'group_first_order_by' => ['type' => SchemaProperty::TYPE_BOOL, "default" => "false"],
        ],
        'PRIMARY_KEY' => 'entity_view_id',
        "INDEXES" => [
            ['properties' => ["owner_id"]],
            ['properties' => ["entity_definition_id"]],
        ]
    ],

    /**
     * Where we store all the modules
     */
    "account_module" => [
        "PROPERTIES" => [
            'account_module_id' => ['type' => SchemaProperty::TYPE_BIGSERIAL],
            'account_id' => ['type' => SchemaProperty::TYPE_UUID, 'notnull' => true],
            'name' => ['type' => SchemaProperty::TYPE_CHAR_256, 'notnull' => true],
            'short_title' => ['type' => SchemaProperty::TYPE_CHAR_256, 'notnull' => true],
            'title' => ['type' => SchemaProperty::TYPE_CHAR_512, 'notnull' => true],
            'scope' => ['type' => SchemaProperty::TYPE_CHAR_32],
            'settings' => ['type' => SchemaProperty::TYPE_CHAR_128],
            'xml_navigation' => ['type' => SchemaProperty::TYPE_CHAR_TEXT],
            'navigation_data' => ['type' => SchemaProperty::TYPE_CHAR_TEXT],
            'f_system' => ['type' => SchemaProperty::TYPE_BOOL, "default" => "false"],
            'user_id' => ['type' => SchemaProperty::TYPE_UUID],
            'team_id' => ['type' => SchemaProperty::TYPE_UUID],
            'sort_order' => ['type' => SchemaProperty::TYPE_SMALLINT],
            'icon' => ['type' => SchemaProperty::TYPE_CHAR_TEXT],
            'default_route' => ['type' => SchemaProperty::TYPE_CHAR_TEXT],
        ],
        'PRIMARY_KEY' => 'account_module_id',
        "INDEXES" => [
            ['properties' => ['account_id']],
            ['properties' => ["user_id"]],
            ['properties' => ["team_id"]],
        ]
    ],

    /**
     * Where all entities are stored
     */
    "entity" => [
        "PROPERTIES" => [
            // Used for inserts and internal/shorthand operations, but guid is the primary key
            'entity_id' => ['type' => SchemaProperty::TYPE_UUID],
            'account_id' => ['type' => SchemaProperty::TYPE_UUID, 'notnull' => true],
            'uname' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'entity_definition_id' => ['type' => SchemaProperty::TYPE_UUID],
            'ts_entered' => ['type' => SchemaProperty::TYPE_TIMESTAMP],
            'ts_updated' => ['type' => SchemaProperty::TYPE_TIMESTAMP],
            'f_deleted' => ['type' => SchemaProperty::TYPE_BOOL, "default" => "false"],
            'commit_id' => ['type' => SchemaProperty::TYPE_BIGINT],
            'field_data' => ['type' => SchemaProperty::TYPE_JSON],
            'schema_version' => ['type' => SchemaProperty::TYPE_INT],
            'tsv_fulltext' => ['type' => SchemaProperty::TYPE_TEXT_TOKENS],
        ],
        'PRIMARY_KEY' => 'entity_id',
        "INDEXES" => [
            ['properties' => ["account_id", "entity_definition_id"]],
            ['type' => 'gin', 'properties' => ["tsv_fulltext"]],
        ]
    ],

    /**
     * Reference of moved entities (like when merged)
     */
    "entity_moved" => [
        "PROPERTIES" => [
            'old_id' => ['type' => SchemaProperty::TYPE_UUID],
            'new_id' => ['type' => SchemaProperty::TYPE_UUID, 'notnull' => true],
        ],
        'PRIMARY_KEY' => ["old_id"],
    ],

    /**
     * Store historical revisions of some entities - if set in definition
     */
    "entity_revision" => [
        "PROPERTIES" => [
            'entity_revision_id' => ['type' => SchemaProperty::TYPE_BIGSERIAL],
            'entity_id' => ['type' => SchemaProperty::TYPE_UUID, 'notnull' => true],
            'revision' => ['type' => SchemaProperty::TYPE_INT],
            'ts_updated' => ['type' => SchemaProperty::TYPE_TIME_WITH_TIME_ZONE],
            'field_data' => ['type' => SchemaProperty::TYPE_JSON],
        ],
        'PRIMARY_KEY' => ['entity_revision_id'],
        "INDEXES" => [
            ['properties' => ["entity_id"]],
        ]
    ],

    /**
     * Store entity recurrence
     */
    "entity_recurrence" => [
        "PROPERTIES" => [
            'entity_recurrence_id' => ['type' => SchemaProperty::TYPE_UUID],
            'account_id' => ['type' => SchemaProperty::TYPE_UUID, 'notnull' => true],
            'entity_definition_id' => ['type' => SchemaProperty::TYPE_UUID, 'notnull' => true],
            'type' => ['type' => SchemaProperty::TYPE_SMALLINT],
            'interval' => ['type' => SchemaProperty::TYPE_SMALLINT],
            'date_processed_to' => ['type' => SchemaProperty::TYPE_DATE],
            'date_start' => ['type' => SchemaProperty::TYPE_DATE],
            'date_end' => ['type' => SchemaProperty::TYPE_DATE],
            't_start' => ['type' => SchemaProperty::TYPE_TIME_WITH_TIME_ZONE],
            't_end' => ['type' => SchemaProperty::TYPE_TIME_WITH_TIME_ZONE],
            'all_day' => ['type' => SchemaProperty::TYPE_BOOL, "default" => "false"],
            'ep_locked' => ['type' => SchemaProperty::TYPE_INT],
            'dayofmonth' => ['type' => SchemaProperty::TYPE_SMALLINT],
            'dayofweekmask' => ['type' => SchemaProperty::TYPE_BOOL_ARRAY],
            'duration' => ['type' => SchemaProperty::TYPE_INT],
            'instance' => ['type' => SchemaProperty::TYPE_SMALLINT],
            'monthofyear' => ['type' => SchemaProperty::TYPE_SMALLINT],
            'parent_entity_id' => ['type' => SchemaProperty::TYPE_UUID],
            'type_id' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'f_active' => ['type' => SchemaProperty::TYPE_BOOL, "default" => "true"],
        ],
        'PRIMARY_KEY' => ['entity_recurrence_id'],
        "INDEXES" => [
            ['properties' => ["date_processed_to"]],
        ]
    ],

    "entity_group" => [
        "PROPERTIES" => [
            'group_id' => ['type' => SchemaProperty::TYPE_UUID],
            'account_id' => ['type' => SchemaProperty::TYPE_UUID, 'notnull' => true],
            'name' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'entity_definition_id' => ['type' => SchemaProperty::TYPE_UUID],
            'field_id' => ['type' => SchemaProperty::TYPE_INT],
            'parent_id' => ['type' => SchemaProperty::TYPE_BIGINT],
            'user_id' => ['type' => SchemaProperty::TYPE_UUID],
            'feed_id' => ['type' => SchemaProperty::TYPE_UUID],
            'color' => ['type' => SchemaProperty::TYPE_CHAR_6],
            'sort_order' => ['type' => SchemaProperty::TYPE_SMALLINT, 'default' => '0'],
            'f_system' => ['type' => SchemaProperty::TYPE_BOOL, "default" => "false"],
            'f_closed' => ['type' => SchemaProperty::TYPE_BOOL, "default" => "false"],
            'commit_id' => ['type' => SchemaProperty::TYPE_BIGINT],
            'filter_values' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'path' => ['type' => SchemaProperty::TYPE_CHAR_256],
        ],
        'PRIMARY_KEY' => ['group_id'],
        "INDEXES" => [
            ['properties' => ["entity_definition_id"]],
            ['properties' => ["field_id"]],
            ['properties' => ["parent_id"]],
            ['properties' => ["user_id"]],
            ['properties' => ["path"]],
        ]
    ],

    /**
     * Store history of commit heads
     */
    "entity_sync_commit_head" => [
        "PROPERTIES" => [
            'type_key' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'head_commit_id' => ['type' => SchemaProperty::TYPE_BIGINT, 'notnull' => true],
        ],
        'PRIMARY_KEY' => 'type_key',
        "INDEXES" => []
    ],

    "entity_sync_partner" => [
        "PROPERTIES" => [
            'entity_sync_partner_id' => ['type' => SchemaProperty::TYPE_BIGSERIAL],
            'pid' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'owner_id' => ['type' => SchemaProperty::TYPE_UUID],
            'ts_last_sync' => ['type' => SchemaProperty::TYPE_TIMESTAMP],
        ],
        'PRIMARY_KEY' => 'entity_sync_partner_id',
        "INDEXES" => [
            ['properties' => ["pid"]],
        ]
    ],

    "entity_sync_collection" => [
        "PROPERTIES" => [
            'entity_sync_collection_id' => ['type' => SchemaProperty::TYPE_BIGSERIAL],
            'type' => ['type' => SchemaProperty::TYPE_INT],
            'partner_id' => ['type' => SchemaProperty::TYPE_INT],
            'entity_definition_id' => ['type' => SchemaProperty::TYPE_UUID],
            'object_type' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'field_id' => ['type' => SchemaProperty::TYPE_INT],
            'field_name' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'ts_last_sync' => ['type' => SchemaProperty::TYPE_TIMESTAMP],
            'conditions' => ['type' => SchemaProperty::TYPE_CHAR_TEXT],
            'f_initialized' => ['type' => SchemaProperty::TYPE_BOOL, "default" => "false"],
            'revision' => ['type' => SchemaProperty::TYPE_BIGINT],
            'last_commit_id' => ['type' => SchemaProperty::TYPE_BIGINT],
        ],
        'PRIMARY_KEY' => 'entity_sync_collection_id',
        "KEYS" => [
            [
                "property" => 'partner_id',
                'references_bucket' => 'object_sync_partners',
                'references_property' => 'id',
            ],
            [
                "property" => 'field_id',
                'references_bucket' => 'app_object_type_fields',
                'references_property' => 'id',
            ],
            [
                "property" => 'entity_definition_id',
                'references_bucket' => 'app_object_types',
                'references_property' => 'id',
            ],
        ],
        "INDEXES" => []
    ],

    "entity_sync_import" => [
        "PROPERTIES" => [
            'entity_sync_import_id' => ['type' => SchemaProperty::TYPE_BIGSERIAL],
            'collection_id' => ['type' => SchemaProperty::TYPE_BIGINT],
            'entity_definition_id' => ['type' => SchemaProperty::TYPE_UUID],
            // Local object id once imported
            'object_id' => ['type' => SchemaProperty::TYPE_UUID],
            // Revision of the local object
            'revision' => ['type' => SchemaProperty::TYPE_INT],
            // This field is depricated and should eventually be deleted
            'parent_id' => ['type' => SchemaProperty::TYPE_BIGINT],
            // This field is depricated and should eventually be deleted
            'field_id' => ['type' => SchemaProperty::TYPE_INT],
            // A revision (usually modified epoch] of the remote object
            'remote_revision' => ['type' => SchemaProperty::TYPE_INT],
            // The unique id of the remote object we have imported
            'unique_id' => ['type' => SchemaProperty::TYPE_CHAR_512],
        ],
        'PRIMARY_KEY' => 'entity_sync_import_id',
        "KEYS" => [
            [
                "property" => 'collection_id',
                'references_bucket' => 'object_sync_partner_collections',
                'references_property' => 'id',
            ],
            [
                "property" => 'entity_definition_id',
                'references_bucket' => 'app_object_types',
                'references_property' => 'id',
            ],
            [
                "property" => 'field_id',
                'references_bucket' => 'app_object_type_fields',
                'references_property' => 'id',
            ],
        ],
        "INDEXES" => [
            ['properties' => ["entity_definition_id", "object_id"]],
            ['properties' => ["field_id", "unique_id"]],
            ['properties' => ["parent_id"]],
        ]
    ],

    "entity_sync_export" => [
        "PROPERTIES" => [
            'entity_sync_export_id' => ['type' => SchemaProperty::TYPE_BIGSERIAL],
            'collection_id' => ['type' => SchemaProperty::TYPE_BIGINT],
            'collection_type' => ['type' => SchemaProperty::TYPE_SMALLINT],
            'commit_id' => ['type' => SchemaProperty::TYPE_BIGINT],
            'new_commit_id' => ['type' => SchemaProperty::TYPE_BIGINT],
            'unique_id' => ['type' => SchemaProperty::TYPE_UUID],
        ],
        "KEYS" => [
            [
                "property" => 'collection_id',
                'references_bucket' => 'object_sync_partner_collections',
                'references_property' => 'id',
            ],
        ],
        "INDEXES" => [
            ['properties' => ["collection_id"]],
            ['properties' => ["unique_id"]],
            ['properties' => ["new_commit_id", "new_commit_id"]],
            ['properties' => ["collection_type", "commit_id"]],
            ['properties' => ["collection_type", "new_commit_id"]],
        ]
    ],

    //
    // ZPush Data Tables
    //
    // The below tables are used with zpush and are not really part of netric.
    // We'll be splitting these out later.
    // -----------------------------------------------------------------------------------
    "async_users" => [
        "PROPERTIES" => [
            'username' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'device_id' => ['type' => SchemaProperty::TYPE_CHAR_64],
        ],
        'PRIMARY_KEY' => ['username', 'device_id'],
    ],

    "async_device_states" => [
        "PROPERTIES" => [
            'id_state' => ['type' => SchemaProperty::TYPE_BIGSERIAL],
            'device_id' => ['type' => SchemaProperty::TYPE_CHAR_64],
            'uuid' => ['type' => SchemaProperty::TYPE_CHAR_64],
            'state_type' => ['type' => SchemaProperty::TYPE_CHAR_64],
            'counter' => ['type' => SchemaProperty::TYPE_INT],
            'state_data' => ['type' => SchemaProperty::TYPE_BINARY_STRING],
            'created_at' => ['type' => SchemaProperty::TYPE_TIMESTAMP],
            'updated_at' => ['type' => SchemaProperty::TYPE_TIMESTAMP],
        ],
        'PRIMARY_KEY' => ['id_state'],
        "INDEXES" => [
            ['properties' => ['device_id', 'uuid', 'state_type', 'counter'], 'type' => 'UNIQUE'],
        ],
    ],
];
