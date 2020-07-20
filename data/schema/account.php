<?php

namespace data\schema;

use Netric\Application\Schema\SchemaProperty;

return [
    /**
     * Generic settings table used fro both user, account, and system settings
     */
    "settings" => [
        "PROPERTIES" => [
            'id' => ['type' => SchemaProperty::TYPE_BIGSERIAL],
            'name' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'value' => ['type' => SchemaProperty::TYPE_CHAR_TEXT],
            'user_id' => ['type' => SchemaProperty::TYPE_INT],
        ],
        'PRIMARY_KEY' => 'id',
        "KEYS" => [
            ['properties' => ["name", "user_id"], 'type' => 'UNIQUE'],
        ],
        "INDEXES" => [
            ['properties' => ["user_id"]],
        ]
    ],
    "app_object_field_defaults" => [
        "PROPERTIES" => [
            'id' => ['type' => SchemaProperty::TYPE_BIGSERIAL],
            'field_id' => ['type' => SchemaProperty::TYPE_INT, 'notnull' => true],
            'on_event' => ['type' => SchemaProperty::TYPE_CHAR_32, 'notnull' => true],
            'value' => ['type' => SchemaProperty::TYPE_CHAR_TEXT, 'notnull' => true],
            'coalesce' => ['type' => SchemaProperty::TYPE_CHAR_TEXT],
            'where_cond' => ['type' => SchemaProperty::TYPE_CHAR_TEXT],
        ],
        'PRIMARY_KEY' => 'id',
        "INDEXES" => [
            ['properties' => ["field_id"]],
        ]
    ],
    "app_object_field_options" => [
        "PROPERTIES" => [
            'id' => ['type' => SchemaProperty::TYPE_BIGSERIAL],
            'field_id' => ['type' => SchemaProperty::TYPE_INT, 'notnull' => true],
            'key' => ['type' => SchemaProperty::TYPE_CHAR_TEXT, 'notnull' => true],
            'value' => ['type' => SchemaProperty::TYPE_CHAR_TEXT, 'notnull' => true],
        ],
        'PRIMARY_KEY' => 'id',
        "INDEXES" => [
            ['properties' => ["field_id"]],
        ]
    ],
    "app_object_type_fields" => [
        "PROPERTIES" => [
            'id' => ['type' => SchemaProperty::TYPE_BIGSERIAL],
            'type_id' => ['type' => SchemaProperty::TYPE_INT],
            'name' => ['type' => SchemaProperty::TYPE_CHAR_128],
            'title' => ['type' => SchemaProperty::TYPE_CHAR_128],
            'type' => ['type' => SchemaProperty::TYPE_CHAR_32],
            'subtype' => ['type' => SchemaProperty::TYPE_CHAR_32],
            'fkey_table_key' => ['type' => SchemaProperty::TYPE_CHAR_128],
            'fkey_multi_tbl' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'fkey_multi_this' => ['type' => SchemaProperty::TYPE_CHAR_128],
            'fkey_multi_ref' => ['type' => SchemaProperty::TYPE_CHAR_128],
            'fkey_table_title' => ['type' => SchemaProperty::TYPE_CHAR_128],
            'sort_order' => ['type' => SchemaProperty::TYPE_INT, "default" => '0'],
            'parent_field' => ['type' => SchemaProperty::TYPE_CHAR_128],
            'autocreatebase' => ["type" => SchemaProperty::TYPE_CHAR_TEXT],
            'autocreatename' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'mask' => ['type' => SchemaProperty::TYPE_CHAR_64],
            'f_readonly' => ['type' => SchemaProperty::TYPE_BOOL, "default" => "false"],
            'autocreate' => ['type' => SchemaProperty::TYPE_BOOL, "default" => "false"],
            'f_system' => ['type' => SchemaProperty::TYPE_BOOL, "default" => "false"],
            'f_required' => ['type' => SchemaProperty::TYPE_BOOL, "default" => "false"],
            'filter' => ["type" => SchemaProperty::TYPE_CHAR_TEXT],
            'use_when' => ["type" => SchemaProperty::TYPE_CHAR_TEXT],
            'f_indexed' => ["type" => SchemaProperty::TYPE_BOOL],
            'f_unique' => ["type" => SchemaProperty::TYPE_BOOL],
        ],
        'PRIMARY_KEY' => 'id',
        "INDEXES" => [
            ['properties' => ["type_id"]],
        ]
    ],

    "app_object_type_frm_layouts" => [
        "PROPERTIES" => [
            'id' => ['type' => SchemaProperty::TYPE_BIGSERIAL],
            'type_id' => ['type' => SchemaProperty::TYPE_INT],
            'team_id' => ['type' => SchemaProperty::TYPE_INT],
            'user_id' => ['type' => SchemaProperty::TYPE_INT],
            'scope' => ["type" => SchemaProperty::TYPE_CHAR_128],
            'form_layout_xml' => ["type" => SchemaProperty::TYPE_CHAR_TEXT],
        ],
        'PRIMARY_KEY' => 'id',
        "INDEXES" => [
            ['properties' => ["type_id"]],
            ['properties' => ["team_id"]],
            ['properties' => ["user_id"]],
            ['properties' => ["scope"]],
        ]
    ],

    "app_object_types" => [
        "PROPERTIES" => [
            'id' => ['type' => SchemaProperty::TYPE_BIGSERIAL],
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
        ],
        'PRIMARY_KEY' => 'id',
        "INDEXES" => [
            ['properties' => ["name"]],
            ['properties' => ["application_id"]],
        ]
    ],

    "app_object_views" => [
        "PROPERTIES" => [
            'id' => ['type' => SchemaProperty::TYPE_BIGSERIAL],
            'name' => ['type' => SchemaProperty::TYPE_CHAR_256, 'notnull' => true],
            'scope' => ['type' => SchemaProperty::TYPE_CHAR_16],
            'description' => ['type' => SchemaProperty::TYPE_CHAR_TEXT],
            'filter_key' => ['type' => SchemaProperty::TYPE_CHAR_TEXT],
            'f_default' => ['type' => SchemaProperty::TYPE_BOOL, "default" => "false"],
            'user_id' => ['type' => SchemaProperty::TYPE_INT],
            'team_id' => ['type' => SchemaProperty::TYPE_INT],
            'object_type_id' => ['type' => SchemaProperty::TYPE_INT],
            'report_id' => ['type' => SchemaProperty::TYPE_INT],
            'owner_id' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'conditions_data' => ['type' => SchemaProperty::TYPE_CHAR_TEXT],
            'order_by_data' => ['type' => SchemaProperty::TYPE_CHAR_TEXT],
            'table_columns_data' => ['type' => SchemaProperty::TYPE_CHAR_TEXT],
            'group_first_order_by' => ['type' => SchemaProperty::TYPE_BOOL, "default" => "false"],
        ],
        'PRIMARY_KEY' => 'id',
        "INDEXES" => [
            ['properties' => ["owner_id"]],
            ['properties' => ["object_type_id"]],
            ['properties' => ["report_id"]],
        ]
    ],

    "applications" => [
        "PROPERTIES" => [
            'id' => ['type' => SchemaProperty::TYPE_BIGSERIAL],
            'name' => ['type' => SchemaProperty::TYPE_CHAR_256, 'notnull' => true],
            'short_title' => ['type' => SchemaProperty::TYPE_CHAR_256, 'notnull' => true],
            'title' => ['type' => SchemaProperty::TYPE_CHAR_512, 'notnull' => true],
            'scope' => ['type' => SchemaProperty::TYPE_CHAR_32],
            'settings' => ['type' => SchemaProperty::TYPE_CHAR_128],
            'xml_navigation' => ['type' => SchemaProperty::TYPE_CHAR_TEXT],
            'navigation_data' => ['type' => SchemaProperty::TYPE_CHAR_TEXT],
            'f_system' => ['type' => SchemaProperty::TYPE_BOOL, "default" => "false"],
            'user_id' => ['type' => SchemaProperty::TYPE_INT],
            'team_id' => ['type' => SchemaProperty::TYPE_INT],
            'sort_order' => ['type' => SchemaProperty::TYPE_SMALLINT],
            'icon' => ['type' => SchemaProperty::TYPE_CHAR_TEXT],
            'default_route' => ['type' => SchemaProperty::TYPE_CHAR_TEXT],
        ],
        'PRIMARY_KEY' => 'id',
        "INDEXES" => [
            ['properties' => ["user_id"]],
            ['properties' => ["team_id"]],
        ]
    ],

    /**
     * New entities table for storing all entities
     */
    "entities" => [
        "PROPERTIES" => [
            // Used for inserts and internal/shorthand operations, but guid is the primary key
            'id' => ['type' => SchemaProperty::TYPE_BIGSERIAL],
            'account_id' => ['type' => SchemaProperty::TYPE_BIGINT],
            'guid' => ['type' => SchemaProperty::TYPE_UUID],
            'uname' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'object_type_id' => ['type' => SchemaProperty::TYPE_INT],
            'ts_entered' => ['type' => SchemaProperty::TYPE_TIMESTAMP],
            'ts_updated' => ['type' => SchemaProperty::TYPE_TIMESTAMP],
            'f_deleted' => ['type' => SchemaProperty::TYPE_BOOL, "default" => "false"],
            'commit_id' => ['type' => SchemaProperty::TYPE_BIGINT],
            'field_data' => ['type' => SchemaProperty::TYPE_JSON],
            'tsv_fulltext' => ['type' => SchemaProperty::TYPE_TEXT_TOKENS],
        ],
        'PRIMARY_KEY' => 'guid',
        "INDEXES" => [
            ['properties' => ["account_id", "object_type_id"]],
            ['type' => 'gin', 'properties' => ["tsv_fulltext"]],
        ]
    ],

    /**
     * Based table where all object tables inherit from
     */
    "objects" => [
        "PROPERTIES" => [
            // id is sequential and unique to a single account
            'id' => ['type' => SchemaProperty::TYPE_BIGSERIAL],
            // global id is unique across all accounts but not sequential
            'guid' => ['type' => SchemaProperty::TYPE_UUID],
            'uname' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'object_type_id' => ['type' => SchemaProperty::TYPE_INT],
            'ts_entered' => ['type' => SchemaProperty::TYPE_TIMESTAMP],
            'ts_updated' => ['type' => SchemaProperty::TYPE_TIMESTAMP],
            'f_deleted' => ['type' => SchemaProperty::TYPE_BOOL, "default" => "false"],
            'tsv_fulltext' => ['type' => SchemaProperty::TYPE_TEXT_TOKENS],
            'commit_id' => ['type' => SchemaProperty::TYPE_BIGINT],
            'field_data' => ['type' => SchemaProperty::TYPE_JSON],
        ]
    ],

    /**
     * Table stores reference of moved objects to another object (like when merged]
     */
    "objects_moved" => [
        "PROPERTIES" => [
            'id' => ['type' => SchemaProperty::TYPE_BIGSERIAL],
            'object_type_id' => ['type' => SchemaProperty::TYPE_BIGINT, 'notnull' => true],
            'object_id' => ['type' => SchemaProperty::TYPE_BIGINT, 'notnull' => true],
            'moved_to' => ['type' => SchemaProperty::TYPE_BIGINT, 'notnull' => true],
        ],
        'PRIMARY_KEY' => ["object_type_id", "object_id"],
    ],

    "object_recurrence" => [
        "PROPERTIES" => [
            'id' => ['type' => SchemaProperty::TYPE_BIGSERIAL],
            'object_type' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'object_type_id' => ['type' => SchemaProperty::TYPE_INT, 'notnull' => true],
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
            'parent_object_id' => ['type' => SchemaProperty::TYPE_BIGINT],
            'type_id' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'f_active' => ['type' => SchemaProperty::TYPE_BOOL, "default" => "true"],
        ],
        'PRIMARY_KEY' => ['id'],
        "INDEXES" => [
            ['properties' => ["date_processed_to"]],
        ]
    ],

    "object_revisions" => [
        "PROPERTIES" => [
            'id' => ['type' => SchemaProperty::TYPE_BIGSERIAL],
            'object_type_id' => ['type' => SchemaProperty::TYPE_INT, 'notnull' => true],
            'object_id' => ['type' => SchemaProperty::TYPE_BIGINT, 'notnull' => true],
            'revision' => ['type' => SchemaProperty::TYPE_INT],
            'ts_updated' => ['type' => SchemaProperty::TYPE_TIME_WITH_TIME_ZONE],
            'data' => ['type' => SchemaProperty::TYPE_CHAR_TEXT],
        ],
        'PRIMARY_KEY' => ['id'],
        "INDEXES" => [
            ['properties' => ["object_type_id", "object_id"]],
        ]
    ],

    "object_groupings" => [
        "PROPERTIES" => [
            'guid' => ['type' => SchemaProperty::TYPE_UUID],
            'id' => ['type' => SchemaProperty::TYPE_BIGSERIAL],
            'name' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'object_type_id' => ['type' => SchemaProperty::TYPE_INT],
            'field_id' => ['type' => SchemaProperty::TYPE_INT],
            'parent_id' => ['type' => SchemaProperty::TYPE_BIGINT],
            'user_id' => ['type' => SchemaProperty::TYPE_INT],
            'feed_id' => ['type' => SchemaProperty::TYPE_INT],
            'color' => ['type' => SchemaProperty::TYPE_CHAR_6],
            'sort_order' => ['type' => SchemaProperty::TYPE_SMALLINT, 'default' => '0'],
            'f_system' => ['type' => SchemaProperty::TYPE_BOOL, "default" => "false"],
            'f_closed' => ['type' => SchemaProperty::TYPE_BOOL, "default" => "false"],
            'commit_id' => ['type' => SchemaProperty::TYPE_BIGINT],
            'filter_values' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'path' => ['type' => SchemaProperty::TYPE_CHAR_256],
        ],
        'PRIMARY_KEY' => ['id'],
        "INDEXES" => [
            ['properties' => ["object_type_id"]],
            ['properties' => ["field_id"]],
            ['properties' => ["parent_id"]],
            ['properties' => ["user_id"]],
            ['properties' => ["path"]],
        ]
    ],

    "workflow_instances" => [
        "PROPERTIES" => [
            'id' => ['type' => SchemaProperty::TYPE_BIGSERIAL],
            'object_type_id' => ['type' => SchemaProperty::TYPE_INT, 'notnull' => true],
            'object_type' => ['type' => SchemaProperty::TYPE_CHAR_128],
            'object_uid' => ['type' => SchemaProperty::TYPE_BIGINT, 'notnull' => true],
            'workflow_id' => ['type' => SchemaProperty::TYPE_INT],
            'ts_started' => ['type' => SchemaProperty::TYPE_TIMESTAMP],
            'ts_completed' => ['type' => SchemaProperty::TYPE_TIMESTAMP],
            'f_completed' => ['type' => SchemaProperty::TYPE_BOOL, "default" => "false"],
        ],
        'PRIMARY_KEY' => 'id',
        "KEYS" => [
            ['properties' => ["object_type_id", "app_object_types", "id"]],
            ['properties' => ["workflow_id", "workflows", "id"]],
        ],
        "INDEXES" => [
            ['properties' => ["object_type"]],
            ['properties' => ["object_uid"]],
        ]
    ],

    /**
     * Store history of commit heads
     */
    "object_sync_commit_heads" => [
        "PROPERTIES" => [
            'id' => ['type' => SchemaProperty::TYPE_BIGSERIAL],
            'type_key' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'head_commit_id' => ['type' => SchemaProperty::TYPE_BIGINT, 'notnull' => true],
        ],
        'PRIMARY_KEY' => 'type_key',
        "INDEXES" => []
    ],

    "object_sync_partners" => [
        "PROPERTIES" => [
            'id' => ['type' => SchemaProperty::TYPE_BIGSERIAL],
            'pid' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'owner_id' => ['type' => SchemaProperty::TYPE_INT],
            'ts_last_sync' => ['type' => SchemaProperty::TYPE_TIMESTAMP],
        ],
        'PRIMARY_KEY' => 'id',
        "KEYS" => [
            [
                "property" => 'owner_id',
                'references_bucket' => 'users',
                'references_property' => 'id',
            ],
        ],
        "INDEXES" => [
            ['properties' => ["pid"]],
        ]
    ],

    "object_sync_partner_collections" => [
        "PROPERTIES" => [
            'id' => ['type' => SchemaProperty::TYPE_BIGSERIAL],
            'type' => ['type' => SchemaProperty::TYPE_INT],
            'partner_id' => ['type' => SchemaProperty::TYPE_INT],
            'object_type_id' => ['type' => SchemaProperty::TYPE_INT],
            'object_type' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'field_id' => ['type' => SchemaProperty::TYPE_INT],
            'field_name' => ['type' => SchemaProperty::TYPE_CHAR_256],
            'ts_last_sync' => ['type' => SchemaProperty::TYPE_TIMESTAMP],
            'conditions' => ['type' => SchemaProperty::TYPE_CHAR_TEXT],
            'f_initialized' => ['type' => SchemaProperty::TYPE_BOOL, "default" => "false"],
            'revision' => ['type' => SchemaProperty::TYPE_BIGINT],
            'last_commit_id' => ['type' => SchemaProperty::TYPE_BIGINT],
        ],
        'PRIMARY_KEY' => 'id',
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
                "property" => 'object_type_id',
                'references_bucket' => 'app_object_types',
                'references_property' => 'id',
            ],
        ],
        "INDEXES" => []
    ],

    "object_sync_import" => [
        "PROPERTIES" => [
            'id' => ['type' => SchemaProperty::TYPE_BIGSERIAL],
            'collection_id' => ['type' => SchemaProperty::TYPE_BIGINT],
            'object_type_id' => ['type' => SchemaProperty::TYPE_INT],
            // Local object id once imported
            'object_id' => ['type' => SchemaProperty::TYPE_BIGINT],
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
        'PRIMARY_KEY' => 'id',
        "KEYS" => [
            [
                "property" => 'collection_id',
                'references_bucket' => 'object_sync_partner_collections',
                'references_property' => 'id',
            ],
            [
                "property" => 'object_type_id',
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
            ['properties' => ["object_type_id", "object_id"]],
            ['properties' => ["field_id", "unique_id"]],
            ['properties' => ["parent_id"]],
        ]
    ],

    "entity_sync_export" => [
        "PROPERTIES" => [
            'id' => ['type' => SchemaProperty::TYPE_BIGSERIAL],
            'collection_id' => ['type' => SchemaProperty::TYPE_BIGINT],
            'collection_type' => ['type' => SchemaProperty::TYPE_SMALLINT],
            'commit_id' => ['type' => SchemaProperty::TYPE_BIGINT],
            'new_commit_id' => ['type' => SchemaProperty::TYPE_BIGINT],
            'unique_id' => ['type' => SchemaProperty::TYPE_BIGINT],
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

    /**
     * The below tables are used with zpush and are not really part of netric.
     * We'll be splitting these out later.
     */
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
