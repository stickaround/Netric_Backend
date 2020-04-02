<?php

/**
 * Schema file for the netric application database
 */

namespace data\schema;

use Netric\Application\Schema\SchemaProperty;

return array(
    /**
     * Main accounts table/bucket
     */
    "accounts" => array(
        "PROPERTIES" => array(
            'id' => array('type' => SchemaProperty::TYPE_BIGSERIAL),
            'name' => array('type' => SchemaProperty::TYPE_CHAR_256),
            'database' => array('type' => SchemaProperty::TYPE_CHAR_256),
            'ts_started' => array('type' => SchemaProperty::TYPE_TIMESTAMP),
            'server' => array('type' => SchemaProperty::TYPE_CHAR_256),
            'version' => array('type' => SchemaProperty::TYPE_CHAR_256),
            'active' => array('type' => SchemaProperty::TYPE_BOOL, 'default' => 't'),
        ),
        'PRIMARY_KEY'       => 'id',
        // TODO: constraints for unique name
        "INDEXES" => array(
            array('properties' => array("name")),
            array('properties' => array("version")),
        )
    ),

    /**
     * Index of users is synchronized with the specific user in the account
     * schema. We use this to very cross-reference users by guid, email, and username.
     */
    "account_users" => array(
        "PROPERTIES" => array(
            'id' => array('type' => SchemaProperty::TYPE_BIGSERIAL),
            'guid' => array('type' => SchemaProperty::TYPE_UUID),
            'account_id' => array('type' => SchemaProperty::TYPE_BIGINT),
            'email_address' => array('type' => SchemaProperty::TYPE_CHAR_256),
            'username' => array('type' => SchemaProperty::TYPE_CHAR_256),
        ),
        'PRIMARY_KEY'       => 'id',
        // TODO: constraints for unique account_id, email_alias
        "KEYS" => array(
            array(
                "property" => 'account_id',
                'references_bucket' => 'accounts',
                'references_property' => 'id',
                'on_delete' => 'cascade',
                'on_update' => 'cascade',
            )
        ),
        "INDEXES" => array(
            array('properties' => array("email_address")),
        )
    ),

    "email_alias" => array(
        "PROPERTIES" => array(
            'address' => array('type' => SchemaProperty::TYPE_CHAR_256),
            'goto' => array('type' => SchemaProperty::TYPE_CHAR_TEXT),
            'active' => array('type' => SchemaProperty::TYPE_BOOL, 'default' => 't'),
            'account_id' => array('type' => SchemaProperty::TYPE_BIGINT),
        ),
        'PRIMARY_KEY' => 'address',
        "KEYS" => array(
            array(
                "property" => 'account_id',
                'references_bucket' => 'accounts',
                'references_property' => 'id',
                'on_delete' => 'cascade',
                'on_update' => 'cascade',
            )
        ),
    ),
    "email_domains" => array(
        "PROPERTIES" => array(
            'domain' => array('type' => SchemaProperty::TYPE_CHAR_256),
            'description' => array('type' => SchemaProperty::TYPE_CHAR_TEXT),
            'active' => array('type' => SchemaProperty::TYPE_BOOL, 'default' => 't'),
            'account_id' => array('type' => SchemaProperty::TYPE_BIGINT),
        ),
        'PRIMARY_KEY' => 'domain',
        "KEYS" => array(
            array(
                "property" => 'account_id',
                'references_bucket' => 'accounts',
                'references_property' => 'id',
                'on_delete' => 'cascade',
                'on_update' => 'cascade',
            )
        ),
    ),
    "email_users" => array(
        "PROPERTIES" => array(
            'id' => array('type' => SchemaProperty::TYPE_BIGSERIAL),
            'email_address' => array('type' => SchemaProperty::TYPE_CHAR_256),
            'maildir' => array('type' => SchemaProperty::TYPE_CHAR_128),
            'password' => array('type' => SchemaProperty::TYPE_CHAR_128),
            'account_id' => array('type' => SchemaProperty::TYPE_BIGINT),
        ),
        'PRIMARY_KEY' => 'id',
        "KEYS" => array(
            array(
                "property" => 'account_id',
                'references_bucket' => 'accounts',
                'references_property' => 'id',
                'on_delete' => 'cascade',
                'on_update' => 'cascade',
            )
        ),
    ),
    "settings" => array(
        "PROPERTIES" => array(
            'id' => array('type' => SchemaProperty::TYPE_BIGSERIAL),
            'name' => array('type' => SchemaProperty::TYPE_CHAR_256),
            'value' => array('type' => SchemaProperty::TYPE_CHAR_TEXT)
        ),
        'PRIMARY_KEY' => 'name',
    ),
    "worker_process_lock" => array(
        "PROPERTIES" => array(
            'id' => array('type' => SchemaProperty::TYPE_BIGSERIAL),
            'process_name' => array('type' => SchemaProperty::TYPE_CHAR_256),
            'ts_entered' => array('type' => SchemaProperty::TYPE_TIMESTAMP),
        ),
        'PRIMARY_KEY' => 'id',
        "INDEXES" => array(
            array('properties' => array("ts_entered")),
            array('properties' => array("process_name"), 'type' => 'UNIQUE'),
        ),
    ),

    // This has been deprecated for gearman
    // and can be deleted once we upgrade V1 to V2
    "worker_job_queue" => array(
        "PROPERTIES" => array(
            'id' => array('type' => SchemaProperty::TYPE_BIGSERIAL),
            'function_name' => array('type' => SchemaProperty::TYPE_CHAR_512),
            'workload' => array('type' => SchemaProperty::TYPE_BINARY_STRING),
            'f_running' => array('type' => SchemaProperty::TYPE_BOOL),
            'account_id' => array('type' => SchemaProperty::TYPE_BIGINT),
            'ts_run' => array('type' => SchemaProperty::TYPE_TIMESTAMP),
            'ts_entered' => array('type' => SchemaProperty::TYPE_TIMESTAMP),
        ),
        'PRIMARY_KEY' => 'id',
        "INDEXES" => array(
            array('properties' => array("ts_run")),
            array('properties' => array("account_id")),
        ),
    ),
    "zipcodes" => array(
        "PROPERTIES" => array(
            'id' => array('type' => SchemaProperty::TYPE_BIGSERIAL),
            'zipcode' => array('type' => SchemaProperty::TYPE_INT),
            'city' => array('type' => SchemaProperty::TYPE_CHAR_64),
            'state' => array('type' => SchemaProperty::TYPE_CHAR_2),
            'latitude' => array('type' => SchemaProperty::TYPE_REAL),
            'longitude' => array('type' => SchemaProperty::TYPE_REAL),
            'dst' => array('type' => SchemaProperty::TYPE_SMALLINT),
            'timezone' => array('type' => SchemaProperty::TYPE_DOUBLE),

        ),
        'PRIMARY_KEY' => 'id',
        "INDEXES" => array(
            array('properties' => array("zipcode")),
        ),
    ),
);
