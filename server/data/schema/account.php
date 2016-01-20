<?php
/**
 * Schema file for an account's database
 *
 * This is the new schema file for netric. All changes to the schema will be entered here
 * and each time 'netric update' is run it will go through every table and make sure
 * every column exists and matches the type.
 *
 * Column drops will need to be handled in the update deltas found in ../../bin/scripts/update/* but now
 * all deltas must assume the newest schema so they will be used for post-update processing,
 * to migrate data, and to clean-up after previous changes.
 */
namespace data\schema;

use Netric\Account\Schema\SchemaProperty;

return array(
    /**
     * Activity types are groupings used to track types
     */
    "activity_types" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'obj_type'		=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'commit_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'obj_type'		=> array("INDEX", "obj_type"),
        )
    ),

    "app_object_field_defaults" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'field_id'		=> array('type'=>SchemaProperty::TYPE_INT, 'notnull'=>true),
            'on_event'		=> array('type'=>SchemaProperty::TYPE_CHAR_32, 'notnull'=>true),
            'value'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT, 'notnull'=>true),
            'coalesce'		=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'where_cond'	=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'field_id'		=> array("INDEX", "field_id"),
        )
    ),

    "app_object_field_options" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'field_id'		=> array('type'=>SchemaProperty::TYPE_INT, 'notnull'=>true),
            'key'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT, 'notnull'=>true),
            'value'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT, 'notnull'=>true),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'field_id'		=> array("INDEX", "field_id"),
        )
    ),


    "app_object_imp_maps" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'template_id'	=> array('type'=>SchemaProperty::TYPE_INT, 'notnull'=>true),
            'col_name'		=> array('type'=>SchemaProperty::TYPE_CHAR_256, 'notnull'=>true),
            'property_name'	=> array('type'=>SchemaProperty::TYPE_CHAR_256, 'notnull'=>true),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'template_id'	=> array("INDEX", "template_id"),
        )
    ),

    "app_object_imp_templates" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'type_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_INT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'type_id'		=> array("INDEX", "type_id"),
            'user_id'		=> array("INDEX", "user_id"),
        )
    ),

    "app_object_list_cache" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'ts_created'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'query'			=> array('type'=>SchemaProperty::TYPE_CHAR_512),
            'total_num'		=> array('type'=>SchemaProperty::TYPE_INT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'query'			=> array("INDEX", "query"),
        )
    ),

    "app_object_list_cache_flds" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'list_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'field_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'value'			=> array('type'=>SchemaProperty::TYPE_CHAR_512),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'list_id'		=> array("INDEX", "list_id"),
            'field_id'		=> array("INDEX", "field_id"),
            'value'			=> array("INDEX", "value"),
        )
    ),

    "app_object_list_cache_res" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'list_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'results'		=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'list_id'		=> array("INDEX", "list_id"),
        )
    ),

    "app_object_type_fields" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'type_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'title'			=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'type'			=> array('type'=>SchemaProperty::TYPE_CHAR_32),
            'subtype'		=> array('type'=>SchemaProperty::TYPE_CHAR_32),
            'fkey_table_key'		=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'fkey_multi_tbl'		=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'fkey_multi_this'		=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'fkey_multi_ref'		=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'fkey_table_title'		=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'sort_order'	=> array('type'=>SchemaProperty::TYPE_INT, "default"=>'0'),
            'parent_field'	=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'autocreatebase'=> array("type"=>SchemaProperty::TYPE_CHAR_TEXT),
            'autocreatename'=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'mask'			=> array('type'=>SchemaProperty::TYPE_CHAR_64),
            'f_readonly'	=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'autocreate'	=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'f_system'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'f_required'	=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'filter'		=> array("type"=>SchemaProperty::TYPE_CHAR_TEXT),
            'use_when'      => array("type"=>SchemaProperty::TYPE_CHAR_TEXT),
            'f_indexed'     => array("type"=>SchemaProperty::TYPE_BOOL),
            'f_unique'      => array("type"=>SchemaProperty::TYPE_BOOL),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'type_id'		=> array("INDEX", "type_id"),
        )
    ),

    "app_object_type_frm_layouts" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'type_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'team_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'scope'			=> array("type"=>SchemaProperty::TYPE_CHAR_128),
            'form_layout_xml'=> array("type"=>SchemaProperty::TYPE_CHAR_TEXT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'type_id'		=> array("INDEX", "type_id"),
            'team_id'		=> array("INDEX", "team_id"),
            'user_id'		=> array("INDEX", "user_id"),
            'scope'			=> array("INDEX", "scope"),
        )
    ),

    "app_object_types" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array("type"=>SchemaProperty::TYPE_CHAR_256),
            'title'			=> array("type"=>SchemaProperty::TYPE_CHAR_256),
            'object_table'	=> array("type"=>"character varying(260)"),
            'revision'		=> array('type'=>SchemaProperty::TYPE_INT, 'default'=>'1'),
            //'label_fields'	=> array("type"=>"character varying(512)"),
            'f_system'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'f_table_created'=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'application_id'=> array('type'=>SchemaProperty::TYPE_INT),
            'capped'		=> array('type'=>SchemaProperty::TYPE_INT),
            'head_commit_id'=> array("type"=>"bigint"),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'name'			=> array("UNIQUE", "name"),
            'application_id'=> array("INDEX", "application_id"),
        )
    ),

    "app_object_view_conditions" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'view_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'field_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'blogic'		=> array("type"=>SchemaProperty::TYPE_CHAR_128, "notnull"=>true),
            'operator'		=> array("type"=>SchemaProperty::TYPE_CHAR_128, "notnull"=>true),
            'value'			=> array("type"=>SchemaProperty::TYPE_CHAR_TEXT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'view_id'=> array("INDEX", "view_id"),
            'field_id'=> array("INDEX", "field_id"),
        )
    ),

    "app_object_view_fields" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'view_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'field_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'sort_order'	=> array('type'=>SchemaProperty::TYPE_INT, 'default'=>'0'),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'view_id'=> array("INDEX", "view_id"),
            'field_id'=> array("INDEX", "field_id"),
        )
    ),

    "app_object_view_orderby" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'view_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'field_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'order_dir'			=> array('type'=>SchemaProperty::TYPE_CHAR_32, 'notnull'=>true),
            'sort_order'	=> array('type'=>SchemaProperty::TYPE_INT, 'default'=>'0'),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'view_id'=> array("INDEX", "view_id"),
            'field_id'=> array("INDEX", "field_id"),
        )
    ),

    "app_object_views" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_256, 'notnull'=>true),
            'scope'			=> array('type'=>SchemaProperty::TYPE_CHAR_16),
            'description'	=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'filter_key'	=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'f_default'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'team_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'object_type_id'=> array('type'=>SchemaProperty::TYPE_INT),
            'report_id'     => array('type'=>SchemaProperty::TYPE_INT),
            'owner_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'conditions_data'=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'order_by_data'=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'table_columns_data'=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'user_id'=> array("INDEX", "user_id"),
            'object_type_id'=> array("INDEX", "object_type_id"),
            'report_id'=> array("INDEX", "report_id"),
        )
    ),

    "app_us_zipcodes" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'zipcode'		=> array('type'=>SchemaProperty::TYPE_INT, 'notnull'=>true),
            'city'			=> array('type'=>SchemaProperty::TYPE_CHAR_64, 'notnull'=>true),
            'state'			=> array('type'=>SchemaProperty::TYPE_CHAR_2),
            'latitude'		=> array('type'=>SchemaProperty::TYPE_REAL, 'notnull'=>true),
            'longitude'		=> array('type'=>SchemaProperty::TYPE_REAL, 'notnull'=>true),
            'dst'			=> array('type'=>SchemaProperty::TYPE_SMALLINT, 'notnull'=>true),
            'timezone'		=> array('type'=>SchemaProperty::TYPE_DOUBLE, 'notnull'=>true),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'zipcode'=> array("INDEX", "zipcode"),
        )
    ),

    "app_widgets" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'title'			=> array('type'=>SchemaProperty::TYPE_CHAR_64, 'notnull'=>true),
            'class_name'	=> array('type'=>SchemaProperty::TYPE_CHAR_64, 'notnull'=>true),
            'file_name'		=> array('type'=>SchemaProperty::TYPE_CHAR_64, 'notnull'=>true),
            'type'			=> array('type'=>SchemaProperty::TYPE_CHAR_32, 'default'=>'system'),
            'description'	=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
        ),
        'PRIMARY_KEY'		=> 'id',
    ),

    "application_calendars" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'calendar_id'	=> array('type'=>SchemaProperty::TYPE_INT, 'notnull'=>true),
            'application_id'=> array('type'=>SchemaProperty::TYPE_INT, 'notnull'=>true),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'calendar_id'=> array("INDEX", "calendar_id"),
            'application_id'=> array("INDEX", "application_id"),
        )
    ),

    "application_objects" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'application_id'=> array('type'=>SchemaProperty::TYPE_INT, 'notnull'=>true),
            'object_type_id'=> array('type'=>SchemaProperty::TYPE_INT, 'notnull'=>true),
            'f_parent_app'	=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'application_id'=> array("INDEX", "application_id"),
            'object_type_id'=> array("INDEX", "object_type_id"),
        )
    ),

    "applications" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_256, 'notnull'=>true),
            'short_title'	=> array('type'=>SchemaProperty::TYPE_CHAR_256, 'notnull'=>true),
            'title'			=> array('type'=>SchemaProperty::TYPE_CHAR_512, 'notnull'=>true),
            'scope'			=> array('type'=>SchemaProperty::TYPE_CHAR_32),
            'settings'		=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'xml_navigation'=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'f_system'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'team_id		'=> array('type'=>SchemaProperty::TYPE_INT),
            'sort_order'	=> array('type'=>SchemaProperty::TYPE_SMALLINT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'user_id'=> array("INDEX", "user_id"),
            'team_id'=> array("INDEX", "team_id"),
        )
    ),

    "async_states" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'key'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'value'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'att_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'time_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'response'		=> array('type'=>SchemaProperty::TYPE_INT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'user_id'=> array("INDEX", "user_id"),
            'key'=> array("INDEX", "key"),
            'att_id'=> array("INDEX", "att_id"),
            'time_id'=> array("INDEX", "time_id"),
        )
    ),

    "calendar_event_coord" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'notes'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'f_deleted'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'revision'		=> array('type'=>SchemaProperty::TYPE_INT),
            'path'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'uname'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'ts_entered'	=> array('type'=>"timestamp with time zone"),
            'ts_updated'	=> array('type'=>"timestamp with time zone"),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(),
    ),

    "calendar_event_coord_times" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'cec_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'ts_start'		=> array('type'=>"timestamp with time zone"),
            'ts_end'		=> array('type'=>"timestamp with time zone"),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'cec_id'		=> array("INDEX", "cec_id"),
        )
    ),

    "calendar_event_coord_att_times" => array(
        "PROPERTIES" => array(
            'att_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'time_id'		=> array('type'=>"integer"),
            'response'		=> array('type'=>"integer"),
        ),
        "KEYS" => array(
            'attend'		=> array("INDEX", "att_id"),
            'time'			=> array("INDEX", "time_id"),
        )
    ),


    "calendar_events" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_512),
            'location'		=> array('type'=>SchemaProperty::TYPE_CHAR_512),
            'notes'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'sharing'		=> array('type'=>SchemaProperty::TYPE_INT),
            'all_day'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'calendar'		=> array('type'=>SchemaProperty::TYPE_INT),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'ts_start'		=> array('type'=>"timestamp with time zone"),
            'ts_end'		=> array('type'=>"timestamp with time zone"),
            'inv_rev'		=> array('type'=>SchemaProperty::TYPE_INT), // event invitation revision
            'inv_uid'		=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT), // remove invitattion id
            'f_deleted'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'revision'		=> array('type'=>SchemaProperty::TYPE_INT),
            'path'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'uname'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'ts_entered'	=> array('type'=>"timestamp with time zone"),
            'ts_updated'    => array('type'=>"timestamp with time zone"),
            'user_status'	=> array('type'=>"integer"),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'user_id'		=> array("FKEY", "user_id", "users", "id"),
            'ts_updated'	=> array("INDEX", "ts_updated"),
            'ts_start'		=> array("INDEX", "ts_start"),
            'ts_end'		=> array("INDEX", "ts_end"),
        )
    ),

    "calendar_events_reminders" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'complete'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'event_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'recur_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'count'			=> array('type'=>SchemaProperty::TYPE_INT),
            'interval'		=> array('type'=>SchemaProperty::TYPE_SMALLINT),
            'type'			=> array('type'=>SchemaProperty::TYPE_SMALLINT),
            'execute_time'	=> array('type'=>"timestamp without time zone"),
            'send_to'		=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'is_snooze'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'event_id'		=> array("INDEX", "event_id"),
            'execute_time'	=> array("INDEX", "execute_time"),
        )
    ),

    "calendar_sharing" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'calendar'		=> array('type'=>SchemaProperty::TYPE_INT),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'accepted'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"true"),
            'f_view'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"true"),
            'color'			=> array('type'=>SchemaProperty::TYPE_CHAR_6),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'calendar_id'	=> array("INDEX", "calendar"),
            'user_id'		=> array("INDEX", "user_id"),
        )
    ),

    "calendars" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'def_cal'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"), // users default
            'f_view'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"true"),
            'color'			=> array('type'=>SchemaProperty::TYPE_CHAR_6),
            'date_created'	=> array('type'=>SchemaProperty::TYPE_DATE),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'user_id'		=> array("INDEX", "user_id"),
            'user_view'		=> array("INDEX", array("user_id", "f_view")),
        )
    ),

    "chat_friends" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'friend_name'	=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'friend_server'	=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'session_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'f_online'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'local_name'	=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'status'		=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'team_id'		=> array('type'=>SchemaProperty::TYPE_INT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'user_id'		=> array("INDEX", "user_id"),
            'team_id'		=> array("INDEX", "team_id"),
        )
    ),

    "chat_queue_agents" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'queue_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_INT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'queue_id'		=> array("INDEX", "queue_id"),
            'user_id'		=> array("INDEX", "user_id"),
        )
    ),

    "chat_queue_entries" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'ts_created'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'session_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'token_id'		=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'queue_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'notes'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'queue_id'		=> array("INDEX", "queue_id"),
            'session_id'	=> array("INDEX", "session_id"),
            'token_id'		=> array("INDEX", "token_id"),
        )
    ),

    "chat_server" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'user_name'		=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'friend_name'	=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'friend_server'	=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'message'		=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'ts_last_message'=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'f_read'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'message_timestamp'=> array('type'=>SchemaProperty::TYPE_INT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'user_id'		=> array("INDEX", "user_id"),
            'f_read'		=> array("INDEX", "f_read"),
            'friend_name'	=> array("INDEX", "friend_name"),
            'message_timestamp'	=> array("INDEX", "message_timestamp"),
            'user_name'		=> array("INDEX", "user_name"),
        )
    ),

    "chat_server_session" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'user_name'		=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'friend_name'	=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'friend_server'	=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'f_typing'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'f_popup'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'f_online'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'f_newmessage'	=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'last_timestamp'=> array('type'=>SchemaProperty::TYPE_INT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'user_id'		=> array("INDEX", "user_id"),
            'user_name'		=> array("INDEX", "user_name"),
            'friend_name'	=> array("INDEX", "friend_name"),
            'friend_server'	=> array("INDEX", "friend_server"),
            'f_popup'		=> array("INDEX", "f_popup"),
        )
    ),

    "comments" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'ts_entered'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'entered_by'	=> array('type'=>SchemaProperty::TYPE_CHAR_512),
            'user_name_chache'=> array('type'=>SchemaProperty::TYPE_CHAR_512),
            'owner_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'comment'		=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'notified'		=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'f_deleted'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'revision'		=> array('type'=>SchemaProperty::TYPE_INT),
            'path'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'uname'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'owner_id'		=> array("INDEX", "owner_id"),
        )
    ),

    "contacts_personal_labels" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_64),
            'color'			=> array('type'=>SchemaProperty::TYPE_CHAR_6),
            'parent_id'		=> array('type'=>SchemaProperty::TYPE_INT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'user_id'		=> array("INDEX", "user_id"),
            'parent_id'		=> array("INDEX", "parent_id"),
        )
    ),

    "contacts_personal_label_mem" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'contact_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'label_id'		=> array('type'=>SchemaProperty::TYPE_INT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'membership'	=> array("UNIQUE", array("contact_id", "label_id")),
        )
    ),

    "customer_association_types" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_64),
            'f_child'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'inherit_fields'=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array()
    ),

    "customer_associations" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'parent_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'customer_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'type_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'relationship_name'=> array('type'=>SchemaProperty::TYPE_CHAR_256),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'customer_id'	=> array("INDEX", "customer_id"),
            'parent_id'		=> array("INDEX", "parent_id"),
            'type_id'		=> array("INDEX", "type_id"),
        )
    ),

    "customer_ccards" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'customer_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'ccard_name'	=> array('type'=>SchemaProperty::TYPE_CHAR_512),
            'ccard_number'	=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'ccard_type'	=> array('type'=>SchemaProperty::TYPE_CHAR_32),
            'ccard_exp_month'=> array('type'=>SchemaProperty::TYPE_SMALLINT),
            'ccard_exp_year'=> array('type'=>SchemaProperty::TYPE_SMALLINT),
            'enc_ver'		=> array('type'=>SchemaProperty::TYPE_CHAR_16),
            'f_default'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'customer_id'	=> array("INDEX", "customer_id"),
        )
    ),

    "customer_invoice_templates" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_512),
            'company_logo'	=> array('type'=>SchemaProperty::TYPE_INT),
            'company_name'	=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'company_slogan'=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'notes_line1'	=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'notes_line2'	=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'footer_line1'	=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'f_deleted'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'revision'		=> array('type'=>SchemaProperty::TYPE_INT),
            'uname'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'path'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'ts_entered'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'ts_updated'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
        )
    ),

    "customer_invoices" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'number'		=> array('type'=>SchemaProperty::TYPE_CHAR_512),
            'owner_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'customer_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'ts_entered'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'ts_updated'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_512),
            'status_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'created_by'	=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'updated_by'	=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'date_due'		=> array('type'=>SchemaProperty::TYPE_DATE),
            'template_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'notes_line1'	=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'notes_line2'	=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'footer_line1'	=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'payment_terms'	=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'send_to'		=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'tax_rate'		=> array('type'=>SchemaProperty::TYPE_INT),
            'amount'		=> array('type'=>SchemaProperty::TYPE_NUMERIC),
            'f_deleted'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'revision'		=> array('type'=>SchemaProperty::TYPE_INT),
            'path'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'uname'         => array('type'=>SchemaProperty::TYPE_CHAR_256),
            'sales_order_id'=> array('type'=>SchemaProperty::TYPE_INT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'owner_id'		=> array("INDEX", "owner_id"),
            'customer_id'	=> array("INDEX", "customer_id"),
            'status_id'		=> array("INDEX", "status_id"),
            'template_id'	=> array("INDEX", "template_id"),
        )
    ),

    "customer_invoice_detail" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'invoice_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'product_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'quantity'		=> array('type'=>SchemaProperty::TYPE_NUMERIC, 'default'=>'1'),
            'amount'		=> array('type'=>SchemaProperty::TYPE_NUMERIC, 'default'=>'0'),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'invoice_id'	=> array("INDEX", "invoice_id"),
            'product_id'	=> array("INDEX", "product_id"),
        )
    ),

    "customer_invoice_status" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'sort_order'	=> array('type'=>SchemaProperty::TYPE_SMALLINT),
            'f_paid'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'commit_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
        )
    ),

    "customer_labels" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_64),
            'parent_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'f_special'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'color'			=> array('type'=>SchemaProperty::TYPE_CHAR_6),
            'commit_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'parent_id'		=> array("INDEX", "parent_id"),
        )
    ),

    "customer_label_mem" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'customer_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'label_id'		=> array('type'=>SchemaProperty::TYPE_INT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'customer_id'	=> array("INDEX", "customer_id"),
            'label_id'		=> array("INDEX", "label_id"),
        )
    ),

    "customer_leads" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'queue_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'owner_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'ts_entered'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'ts_updated'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'first_name'	=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'last_name'		=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'email'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'phone'			=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'street'		=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'street2'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'city'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'state'			=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'zip'			=> array('type'=>SchemaProperty::TYPE_CHAR_32),
            'notes'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'source_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'rating_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'status_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'company'		=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'title'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'website'		=> array('type'=>SchemaProperty::TYPE_CHAR_512),
            'country'		=> array('type'=>SchemaProperty::TYPE_CHAR_512),
            'customer_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'opportunity_id'=> array('type'=>SchemaProperty::TYPE_INT),
            'ts_first_contacted'=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'ts_last_contacted'=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'class_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'phone2'		=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'phone3'		=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'fax'			=> array('type'=>SchemaProperty::TYPE_CHAR_64),
            'f_deleted'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'revision'		=> array('type'=>SchemaProperty::TYPE_INT),
            'path'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'uname'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'customer_id'	=> array("INDEX", "customer_id"),
            'source_id'		=> array("INDEX", "source_id"),
            'rating_id'		=> array("INDEX", "rating_id"),
            'status_id'		=> array("INDEX", "status_id"),
            'opportunity_id'=> array("INDEX", "opportunity_id"),
        )
    ),

    "customer_lead_status" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'sort_order'	=> array('type'=>SchemaProperty::TYPE_SMALLINT, 'default'=>'0'),
            'f_closed'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'f_converted'	=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'color'			=> array('type'=>SchemaProperty::TYPE_CHAR_6),
            'commit_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
        )
    ),

    "customer_lead_sources" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'sort_order'	=> array('type'=>SchemaProperty::TYPE_SMALLINT, 'default'=>'0'),
            'color'			=> array('type'=>SchemaProperty::TYPE_CHAR_6),
            'commit_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
        )
    ),

    "customer_lead_rating" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'sort_order'	=> array('type'=>SchemaProperty::TYPE_SMALLINT, 'default'=>'0'),
            'color'			=> array('type'=>SchemaProperty::TYPE_CHAR_6),
            'commit_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
        )
    ),

    "customer_lead_classes" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'sort_order'	=> array('type'=>SchemaProperty::TYPE_SMALLINT, 'default'=>'0'),
            'color'			=> array('type'=>SchemaProperty::TYPE_CHAR_6),
            'commit_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
        )
    ),

    "customer_lead_queues" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'sort_order'	=> array('type'=>SchemaProperty::TYPE_SMALLINT, 'default'=>'0'),
            'color'			=> array('type'=>SchemaProperty::TYPE_CHAR_6),
            'dacl_edit'		=> array('type'=>SchemaProperty::TYPE_INT),
            'commit_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
        )
    ),

    "customer_objections" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'description'	=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'sort_order'	=> array('type'=>SchemaProperty::TYPE_SMALLINT, 'default'=>'0'),
            'color'			=> array('type'=>SchemaProperty::TYPE_CHAR_6),
            'commit_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
        )
    ),

    "customer_opportunities" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'owner_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'ts_entered'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'ts_updated'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'notes'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'stage_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'expected_close_date'=> array('type'=>SchemaProperty::TYPE_DATE),
            'amount'		=> array('type'=>SchemaProperty::TYPE_NUMERIC),
            'customer_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'lead_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'lead_source_id'=> array('type'=>SchemaProperty::TYPE_INT),
            'probability_per'=> array('type'=>SchemaProperty::TYPE_SMALLINT),
            'created_by'	=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'type_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'updated_by'	=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'ts_closed'		=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'ts_first_contacted'=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'ts_last_contacted'=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'objection_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'closed_lost_reson'=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'f_deleted'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'revision'		=> array('type'=>SchemaProperty::TYPE_INT),
            'path'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'uname'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'stage_id'		=> array("INDEX", "stage_id"),
            'customer_id'	=> array("INDEX", "customer_id"),
            'lead_id'		=> array("INDEX", "lead_id"),
            'lead_source_id'=> array("INDEX", "lead_source_id"),
            'type_id'		=> array("INDEX", "type_id"),
        )
    ),

    "customer_opportunity_stages" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'sort_order'	=> array('type'=>SchemaProperty::TYPE_SMALLINT, 'default'=>'0'),
            'color'			=> array('type'=>SchemaProperty::TYPE_CHAR_6),
            'f_closed'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'f_won'			=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'commit_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
        )
    ),

    "customer_opportunity_types" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'sort_order'	=> array('type'=>SchemaProperty::TYPE_SMALLINT, 'default'=>'0'),
            'color'			=> array('type'=>SchemaProperty::TYPE_CHAR_6),
            'commit_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
        )
    ),

    "customer_publish" => array(
        "PROPERTIES" => array(
            'customer_id'	=> array('type'=>SchemaProperty::TYPE_BIGINT, 'notnull'=>true),
            'username'		=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'password'		=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'f_files_view'	=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'f_files_upload'=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'f_files_modify'=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'f_modify_contact'=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'f_update_image'=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
        ),
        'PRIMARY_KEY'		=> 'customer_id',
        "KEYS" => array(
            'username'		=> array("INDEX", "username"),
            'password'		=> array("INDEX", "password"),
        )
    ),

    "customer_stages" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'sort_order'	=> array('type'=>SchemaProperty::TYPE_SMALLINT, 'default'=>'0'),
            'color'			=> array('type'=>SchemaProperty::TYPE_CHAR_6),
            'commit_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
        )
    ),

    "customer_status" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'sort_order'	=> array('type'=>SchemaProperty::TYPE_SMALLINT, 'default'=>'0'),
            'color'			=> array('type'=>SchemaProperty::TYPE_CHAR_6),
            'f_closed'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'commit_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
        )
    ),

    "customers" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_512),
            'first_name'	=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'last_name'		=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'company'		=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'salutation'	=> array('type'=>SchemaProperty::TYPE_CHAR_512),
            'email'			=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'email2'		=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'email3'		=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'phone_home'	=> array('type'=>SchemaProperty::TYPE_CHAR_64),
            'phone_work'	=> array('type'=>SchemaProperty::TYPE_CHAR_64),
            'phone_cell'	=> array('type'=>SchemaProperty::TYPE_CHAR_64),
            'phone_other'	=> array('type'=>SchemaProperty::TYPE_CHAR_64),
            'street'		=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'street_2'		=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'city'			=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'zip'			=> array('type'=>SchemaProperty::TYPE_CHAR_32),
            'time_entered'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'job_title'		=> array('type'=>SchemaProperty::TYPE_CHAR_64),
            'phone_fax'		=> array('type'=>SchemaProperty::TYPE_CHAR_64),
            'phone_pager'	=> array('type'=>SchemaProperty::TYPE_CHAR_64),
            'middle_name'	=> array('type'=>SchemaProperty::TYPE_CHAR_64),
            'time_changed'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'email_default'	=> array('type'=>SchemaProperty::TYPE_CHAR_16),
            'spouse_name'	=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'business_street'=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'business_street_2'=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'business_city'	=> array('type'=>SchemaProperty::TYPE_CHAR_64),
            'business_state'=> array('type'=>SchemaProperty::TYPE_CHAR_64),
            'business_zip'	=> array('type'=>SchemaProperty::TYPE_CHAR_16),
            'phone_ext'		=> array('type'=>SchemaProperty::TYPE_CHAR_16),
            'website'		=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'birthday'		=> array('type'=>SchemaProperty::TYPE_DATE),
            'birthday_spouse'=> array('type'=>SchemaProperty::TYPE_DATE),
            'anniversary'	=> array('type'=>SchemaProperty::TYPE_DATE),
            'last_contacted'=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'nick_name'		=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'source'		=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'status'		=> array('type'=>SchemaProperty::TYPE_CHAR_64),
            'email_spouse'	=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'source_notes'	=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'contacted'		=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'notes'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'type_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'address_default'=> array('type'=>SchemaProperty::TYPE_CHAR_16),
            'f_nocall'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'f_noemailspam'	=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'f_nocontact'	=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'status_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'stage_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'owner_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'address_billing'=> array('type'=>SchemaProperty::TYPE_CHAR_16),
            'ts_first_contacted'=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'shipping_street'=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'shipping_street2'=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'shipping_city'	=> array('type'=>SchemaProperty::TYPE_CHAR_64),
            'shipping_state'=> array('type'=>SchemaProperty::TYPE_CHAR_64),
            'shipping_zip'	=> array('type'=>SchemaProperty::TYPE_CHAR_16),
            'billing_street'=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'billing_street2'=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'billing_city'	=> array('type'=>SchemaProperty::TYPE_CHAR_64),
            'billing_state'=> array('type'=>SchemaProperty::TYPE_CHAR_64),
            'billing_zip'	=> array('type'=>SchemaProperty::TYPE_CHAR_16),
            'f_deleted'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'revision'		=> array('type'=>SchemaProperty::TYPE_INT),
            'path'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'uname'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'owner_id'		=> array("INDEX", "owner_id"),
            'stage_id'		=> array("INDEX", "stage_id"),
            'status_id'		=> array("INDEX", "status_id"),
            'type_id'		=> array("INDEX", "type_id"),
            'email'			=> array("INDEX", "email"),
            'email2'		=> array("INDEX", "email2"),
        )
    ),

    "discussions" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_512),
            'message'		=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'notified'		=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'ts_entered'=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'ts_updated'=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'owner_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'f_deleted'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'revision'		=> array('type'=>SchemaProperty::TYPE_INT),
            'path'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'uname'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'owner_id'		=> array("INDEX", "owner_id"),
        )
    ),

    "email_accounts" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_INT, 'default'=>'auto_increment'),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_512, 'notnull'=>true),
            'address'		=> array('type'=>SchemaProperty::TYPE_CHAR_512, 'notnull'=>true),
            'reply_to'		=> array('type'=>SchemaProperty::TYPE_CHAR_512),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'ts_last_full_sync'=> array('type'=>SchemaProperty::TYPE_INT),
            'f_default'     => array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'signature'     => array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'type'          => array('type'=>SchemaProperty::TYPE_CHAR_16),
            'username'      => array('type'=>SchemaProperty::TYPE_CHAR_128),
            'password'      => array('type'=>SchemaProperty::TYPE_CHAR_128),
            'host'          => array('type'=>SchemaProperty::TYPE_CHAR_128),
            'port'          => array('type'=>SchemaProperty::TYPE_SMALLINT),
            'ssl'           => array('type'=>SchemaProperty::TYPE_CHAR_8),
            'sync_data'     => array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'f_ssl' 	   	=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'f_system'    	=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'f_outgoing_auth'=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'host_out'      => array('type'=>SchemaProperty::TYPE_CHAR_128),
            'port_out'      => array('type'=>SchemaProperty::TYPE_CHAR_8),
            'f_ssl_out'	   	=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'username_out'  => array('type'=>SchemaProperty::TYPE_CHAR_128),
            'password_out'  => array('type'=>SchemaProperty::TYPE_CHAR_128),
            'forward'       => array('type'=>SchemaProperty::TYPE_CHAR_256),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'user_id'		=> array("INDEX", "user_id", "users", "id"),
            'address'		=> array("INDEX", "address"),
        )
    ),


    "email_video_message_themes" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_INT, 'default'=>'auto_increment'),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_256, 'notnull'=>true),
            'html'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'header_file_id'=> array('type'=>SchemaProperty::TYPE_INT),
            'footer_file_id'=> array('type'=>SchemaProperty::TYPE_INT),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'background_color'=> array('type'=>SchemaProperty::TYPE_CHAR_6),
            'scope'			=> array('type'=>SchemaProperty::TYPE_CHAR_32),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'user_id'		=> array("INDEX", "user_id", "users", "id"),
            'scope'			=> array("INDEX", "scope"),
        )
    ),

    "email_filters" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_INT, 'default'=>'auto_increment'),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_512, 'notnull'=>true),
            'kw_subject'	=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'kw_to'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'kw_from'		=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'kw_body'		=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'f_active'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"true"),
            'act_mark_read'	=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'act_move_to'	=> array('type'=>SchemaProperty::TYPE_INT),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_INT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'user_id'		=> array("INDEX", "user_id", "users", "id"),
        )
    ),

    "email_settings_spam" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_INT, 'default'=>'auto_increment'),
            'preference'	=> array('type'=>SchemaProperty::TYPE_CHAR_32, 'notnull'=>true),
            'value'			=> array('type'=>SchemaProperty::TYPE_CHAR_128, 'notnull'=>true),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_INT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'user_id'		=> array("INDEX", "user_id", "users", "id"),
        )
    ),

    "email_mailboxes" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_64),
            'parent_box'	=> array('type'=>SchemaProperty::TYPE_INT),
            'flag_special'	=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'f_system'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'sort_order'	=> array('type'=>SchemaProperty::TYPE_SMALLINT, 'default'=>'0'),
            'color'			=> array('type'=>SchemaProperty::TYPE_CHAR_6),
            'user_id'       => array('type'=>SchemaProperty::TYPE_INT),
            'type'          => array('type'=>SchemaProperty::TYPE_CHAR_16),
            'mailbox'		=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'commit_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'user_id'		=> array("INDEX", "user_id", "users", "id"),
            'parent_box'	=> array("INDEX", "parent_box"),
        )
    ),


    "email_message_original" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'message_id'	=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'file_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'lo_message'	=> array('type'=>SchemaProperty::TYPE_BINARY_OID),
            'antmail_version'=> array('type'=>SchemaProperty::TYPE_INT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'message_id'	=> array("INDEX", "message_id", "objects_email_messages", "id"),
        )
    ),

    "email_message_queue" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'lo_message'	=> array('type'=>SchemaProperty::TYPE_BINARY_OID),
            'ts_delivered'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'user_id'		=> array("INDEX", "user_id", "users", "id"),
        )
    ),

    "email_thread_mailbox_mem" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'thread_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'mailbox_id'	=> array('type'=>SchemaProperty::TYPE_INT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'mailbox_id'	=> array("INDEX", "mailbox_id", "email_mailboxes", "id"),
            'thread_id'		=> array("INDEX", "thread_id", "email_threads", "id"),
        )
    ),

    "email_video_messages" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'file_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'title'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'subtitle'		=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'message'		=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'footer'		=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'theme'			=> array('type'=>SchemaProperty::TYPE_CHAR_64),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'logo_file_id'	=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'f_template_video'=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'facebook'		=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'twitter'		=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'user_id'		=> array("INDEX", "user_id", "users", "id"),
        )
    ),

    "favorites_categories" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'parent_id'		=> array('type'=>SchemaProperty::TYPE_INT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'user_id'		=> array("INDEX", "user_id", "users", "id"),
            'parent_id'		=> array("INDEX", "parent_id", "favorites_categories", "id"),
        )
    ),

    "favorites" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_64),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'favorite_category'	=> array('type'=>SchemaProperty::TYPE_INT),
            'url'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'notes'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'user_id'		=> array("INDEX", "user_id", "users", "id"),
            'favorite_category'	=> array("INDEX", "favorite_category", "favorites_categories", "id"),
        )
    ),

    "ic_groups" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'parent_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_128, 'notnull'=>true),
            'color'			=> array('type'=>SchemaProperty::TYPE_CHAR_6),
            'sort_order'	=> array('type'=>SchemaProperty::TYPE_SMALLINT, 'default'=>'0'),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'parent_id'		=> array("INDEX", "parent_id"),
        )
    ),

    "ic_document_group_mem" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'document_id'	=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'group_id'		=> array('type'=>SchemaProperty::TYPE_INT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'document_id'	=> array("INDEX", "document_id", "ic_documents", "id"),
            'group_id'		=> array("INDEX", "group_id", "ic_groups", "id"),
        ),
    ),


    "ic_documents" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'title'			=> array('type'=>SchemaProperty::TYPE_CHAR_512),
            'keywords'		=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'body'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'video_file_id'	=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'ts_entered'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'ts_updated'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'owner_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'f_deleted'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'revision'		=> array('type'=>SchemaProperty::TYPE_INT),
            'path'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'uname'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'owner_id'		=> array("FKEY", "owner_id", "users", "id"),
        )
    ),


    "members" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_512),
            'role'			=> array('type'=>SchemaProperty::TYPE_CHAR_512),
            'ts_entered'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'ts_updated'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'f_accepted'	=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"true"),
            'f_required'	=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'f_invsent'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'f_deleted'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'revision'		=> array('type'=>SchemaProperty::TYPE_INT),
            'path'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'uname'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
        )
    ),

    /**
     * Based table where all object tables inherit from
     */
    "objects" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'object_type_id'=> array('type'=>SchemaProperty::TYPE_INT),
            'revision'		=> array('type'=>SchemaProperty::TYPE_INT),
            'ts_entered'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'ts_updated'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'owner_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'owner_id_fval'	=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'creator_id'	=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'creator_id_fval'=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'f_deleted'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'uname'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'path'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'dacl'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'tsv_fulltext'	=> array('type'=>SchemaProperty::TYPE_TEXT_TOKENS),
            'num_comments'	=> array('type'=>SchemaProperty::TYPE_INT),
            'commit_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
        )
    ),

    /**
     * Table stores refrence of moved objects to another object (like when merged)
     */
    "objects_moved" => array(
        "PROPERTIES" => array(
            'object_type_id'=> array('type'=>SchemaProperty::TYPE_BIGINT, 'notnull'=>true),
            'object_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT, 'notnull'=>true),
            'moved_to'		=> array('type'=>SchemaProperty::TYPE_BIGINT, 'notnull'=>true),
        ),
        'PRIMARY_KEY'		=> array("object_type_id", "object_id"),
        "KEYS" => array(
            'movedto'		=> array("INDEX", array("object_type_id", "moved_to"))
        )
    ),

    /**
     * Store multi-dim references between objects (related to / associated with)
     */
    "object_associations" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'type_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'object_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'assoc_type_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'assoc_object_id'=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'field_id'		=> array('type'=>SchemaProperty::TYPE_INT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'assocobj'		=> array("INDEX", array("assoc_type_id", "assoc_object_id", "field_id")),
            'fld'			=> array("INDEX", array("type_id", "object_id", "field_id")),
            'refobj'		=> array("INDEX", array("type_id", "assoc_type_id", "field_id")),
            SchemaProperty::TYPE_BINARY_OID			=> array("INDEX", array("object_id")),
            'type'			=> array("INDEX", array("type_id")),
        )
    ),

    /**
     * Store indexe data for initialization and schema updates mostly
     */
    "object_indexes" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_INT, 'notnull'=>true),
            'revision'		=> array('type'=>SchemaProperty::TYPE_INT),
        )
    ),


    /**
     * @depriacted
     * We are leaving these for reference in case we decide to use oracle which is
     * a whole lot better at very thin and long tables index queries and sorts.
     *
    "object_index" => array(
    "PROPERTIES" => array(
    'object_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT, 'notnull'=>true),
    'object_type_id'=> array('type'=>SchemaProperty::TYPE_INT, 'notnull'=>true),
    'field_id'		=> array('type'=>SchemaProperty::TYPE_INT),
    'val_text'		=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
    'val_tsv'		=> array('type'=>SchemaProperty::TYPE_TEXT_TOKENS),
    'val_number'	=> array('type'=>SchemaProperty::TYPE_NUMERIC),
    'val_bool'		=> array('type'=>SchemaProperty::TYPE_BOOL),
    'val_timestamp'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
    'f_deleted'		=> array('type'=>SchemaProperty::TYPE_BOOL),
    )
    ),

    "object_index_cachedata" => array(
    "PROPERTIES" => array(
    'object_type_id'=> array('type'=>SchemaProperty::TYPE_INT, 'notnull'=>true),
    'object_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT, 'notnull'=>true),
    'revision'		=> array('type'=>SchemaProperty::TYPE_INT),
    'data'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
    ),
    'PRIMARY_KEY'		=> array('object_type_id', 'object_id'),
    "KEYS" => array(
    'object_type_id'=> array("FKEY", "object_type_id", "app_object_types", "id"),
    ),
    ),

    // No keys because this is an abstract table inherited
    "object_index_fulltext" => array(
    "PROPERTIES" => array(
    'object_type_id'=> array('type'=>SchemaProperty::TYPE_INT, 'notnull'=>true),
    'object_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT, 'notnull'=>true),
    'object_revision'=> array('type'=>SchemaProperty::TYPE_INT),
    'f_deleted'		=> array('type'=>SchemaProperty::TYPE_BOOL),
    'snippet'		=> array('type'=>SchemaProperty::TYPE_CHAR_512),
    'private_owner_id'=> array('type'=>SchemaProperty::TYPE_INT),
    'ts_entered'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
    'tsv_keywords'	=> array('type'=>SchemaProperty::TYPE_TEXT_TOKENS),
    )
    ),

    "object_index_fulltext_act" => array(
    'COLUMNS' => array(), // inherited
    'INHERITS' => 'object_index_fulltext',
    'PRIMARY_KEY' => array('object_type_id', 'object_id'),
    "CONSTRAINTS" => array(
    'actcheck'=> "f_deleted = false",
    ),
    "KEYS" => array(
    'object_type_id'=> array("INDEX", "object_type_id", "app_object_types", "id"),
    'private_owner'=> array("INDEX", "private_owner_id", "users", "id"),
    'keywords'	=> array("INDEX", "tsv_keywords"),
    ),
    );

    "object_index_fulltext_del" => array(
    'COLUMNS' => array(), // inherited
    'INHERITS' => 'object_index_fulltext',
    'PRIMARY_KEY'		=> array('object_type_id', 'object_id'),
    "CONSTRAINTS" => array(
    'actcheck'=> "f_deleted = true",
    ),
    "KEYS" => array(
    'object_type_id'=> array("INDEX", "object_type_id", "app_object_types", "id"),
    'private_owner'	=> array("INDEX", "private_owner_id", "users", "id"),
    'keywords'	=> array("INDEX", "tsv_keywords"),
    ),
    ),
     */

    /**
     * Historical log used to indicate when an object has been indexed so that
     * we can reconsile with a background script to make sure we did not miss anything.
     */
    "object_indexed" => array(
        "PROPERTIES" => array(
            'object_type_id'=> array('type'=>SchemaProperty::TYPE_INT, 'notnull'=>true),
            'object_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT, 'notnull'=>true),
            'revision'		=> array('type'=>SchemaProperty::TYPE_INT),
            'index_type'	=> array('type'=>SchemaProperty::TYPE_SMALLINT),
        ),
        'PRIMARY_KEY'		=> array('object_type_id', 'object_id'),
        "KEYS" => array(
            'index_type'	=> array("INDEX", "index_type"),
            'object_id'		=> array("INDEX", "object_id"),
            'object_type_id'=> array("INDEX", "object_type_id"),
        ),
    ),

    "object_recurrence" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'object_type'	=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'object_type_id'=> array('type'=>SchemaProperty::TYPE_INT, 'notnull'=>true),
            'type'			=> array('type'=>SchemaProperty::TYPE_SMALLINT),
            'interval'		=> array('type'=>SchemaProperty::TYPE_SMALLINT),
            'date_processed_to'	=> array('type'=>SchemaProperty::TYPE_DATE),
            'date_start'	=> array('type'=>SchemaProperty::TYPE_DATE),
            'date_end'		=> array('type'=>SchemaProperty::TYPE_DATE),
            't_start'		=> array('type'=>SchemaProperty::TYPE_TIME_WITH_TIME_ZONE),
            't_end'			=> array('type'=>SchemaProperty::TYPE_TIME_WITH_TIME_ZONE),
            'all_day'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'ep_locked'		=> array('type'=>SchemaProperty::TYPE_INT),
            'dayofmonth'	=> array('type'=>SchemaProperty::TYPE_SMALLINT),
            'dayofweekmask'	=> array('type'=>SchemaProperty::TYPE_BOOL_ARRAY),
            'duration'		=> array('type'=>SchemaProperty::TYPE_INT),
            'instance'		=> array('type'=>SchemaProperty::TYPE_SMALLINT),
            'monthofyear'	=> array('type'=>SchemaProperty::TYPE_SMALLINT),
            'parent_object_id'	=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'type_id'		=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'f_active'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"true"),
        ),
        'PRIMARY_KEY'		=> array('id'),
        "KEYS" => array(
            'date_processed_to'	=> array("INDEX", "date_processed_to"),
        ),
    ),

    "object_revisions" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'object_type_id'=> array('type'=>SchemaProperty::TYPE_INT, 'notnull'=>true),
            'object_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT, 'notnull'=>true),
            'revision'		=> array('type'=>SchemaProperty::TYPE_INT),
            'ts_updated'	=> array('type'=>SchemaProperty::TYPE_TIME_WITH_TIME_ZONE),
            'data'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
        ),
        'PRIMARY_KEY'		=> array('id'),
        "KEYS" => array(
            'object'	=> array("INDEX", array("object_type_id", "object_id")),
        ),
    ),

    "object_revision_data" => array(
        "PROPERTIES" => array(
            'revision_id'	=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'field_name'	=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'field_value'	=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
        ),
        "KEYS" => array(
            'revision_id'	=> array("INDEX", 'revision_id', "object_revisions", "id"),
        ),
    ),

    "object_unames" => array(
        "PROPERTIES" => array(
            'object_type_id'=> array('type'=>SchemaProperty::TYPE_INT, 'notnull'=>true),
            'object_id'	=> array('type'=>SchemaProperty::TYPE_BIGINT, 'notnull'=>true),
            'name'		=> array('type'=>SchemaProperty::TYPE_CHAR_512),
        ),
        'PRIMARY_KEY'	=> array('object_type_id', 'object_id'),
        "KEYS" => array(
            'uname'		=> array("INDEX", array("object_type_id", "name")),
        ),
    ),

    "object_groupings" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'object_type_id'=> array('type'=>SchemaProperty::TYPE_INT),
            'field_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'parent_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'color'			=> array('type'=>SchemaProperty::TYPE_CHAR_6),
            'sort_order'	=> array('type'=>SchemaProperty::TYPE_SMALLINT, 'default'=>'0'),
            'f_system'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'f_closed'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'commit_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
        ),
        'PRIMARY_KEY'		=> array('id'),
        "KEYS" => array(
            'object_type_id'=> array("INDEX", array("object_type_id")),
            'field'			=> array("INDEX", array("field_id")),
            'parent'		=> array("INDEX", array("parent_id")),
            'user'			=> array("INDEX", array("user_id")),
        )
    ),

    "object_grouping_mem" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'object_type_id'=> array('type'=>SchemaProperty::TYPE_INT, 'notnull'=>true),
            'object_id'		=> array('type'=>SchemaProperty::TYPE_INT, 'notnull'=>true),
            'field_id'		=> array('type'=>SchemaProperty::TYPE_INT, 'notnull'=>true),
            'grouping_id'	=> array('type'=>SchemaProperty::TYPE_INT, 'notnull'=>true),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'object'		=> array("INDEX", array("object_type_id", "object_id")),
            'field_id'		=> array("INDEX", "field_id"),
            'group'			=> array("INDEX", "grouping_id", "object_groupings", "id"),
        )
    ),

    "printing_papers_labels" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_INT, 'default'=>'auto_increment'),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_32),
            'cols'			=> array('type'=>SchemaProperty::TYPE_SMALLINT),
            'y_start_pos'	=> array('type'=>SchemaProperty::TYPE_SMALLINT),
            'y_interval'	=> array('type'=>SchemaProperty::TYPE_SMALLINT),
            'x_pos'			=> array('type'=>SchemaProperty::TYPE_CHAR_32),
        ),
        'PRIMARY_KEY'		=> array('id'),
        "KEYS" => array(
        ),
    ),

    "product_categories" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'parent_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_128, 'notnull'=>true),
            'color'			=> array('type'=>SchemaProperty::TYPE_CHAR_6),
            'sort_order'	=> array('type'=>SchemaProperty::TYPE_SMALLINT, 'default'=>'0'),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
        )
    ),

    "product_categories_mem" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'product_id'	=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'category_id'	=> array('type'=>SchemaProperty::TYPE_INT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'product_id'	=> array("INDEX", "product_id", "products", "id"),
            'category_id'	=> array("INDEX", "category_id", "product_categories", "id"),
        )
    ),

    "product_families" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'notes'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'ts_entered'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'ts_updated'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'f_deleted'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'revision'		=> array('type'=>SchemaProperty::TYPE_INT),
            'uname'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'path'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
        )
    ),

    "products" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'notes'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'price'			=> array('type'=>SchemaProperty::TYPE_NUMERIC),
            'f_available'	=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'rating'		=> array('type'=>SchemaProperty::TYPE_NUMERIC),
            'family'		=> array('type'=>SchemaProperty::TYPE_NUMERIC),
            'image_id'		=> array('type'=>SchemaProperty::TYPE_NUMERIC),
            'ts_entered'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'ts_updated'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'f_deleted'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'revision'		=> array('type'=>SchemaProperty::TYPE_INT),
            'uname'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'path'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'image_id'		=> array("INDEX", "image_id"),
            'family'		=> array("FKEY", "family", "product_families", "id"),
        )
    ),

    "product_reviews" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'notes'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'creator_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'rating'		=> array('type'=>SchemaProperty::TYPE_NUMERIC),
            'product'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'ts_entered'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'ts_updated'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'f_deleted'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'revision'		=> array('type'=>SchemaProperty::TYPE_INT),
            'uname'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'path'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'product_id'	=> array("FKEY", "product_id", "products", "id"),
        )
    ),

    /**
     * The project_bug* tables are where cases are stored for legacy reasons
     */
    "project_bug_severity" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_128, 'notnull'=>true),
            'color'			=> array('type'=>SchemaProperty::TYPE_CHAR_6),
            'sort_order'	=> array('type'=>SchemaProperty::TYPE_SMALLINT, 'default'=>'0'),
            'commit_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
        )
    ),

    "project_bug_status" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_128, 'notnull'=>true),
            'color'			=> array('type'=>SchemaProperty::TYPE_CHAR_6),
            'sort_order'	=> array('type'=>SchemaProperty::TYPE_SMALLINT, 'default'=>'0'),
            'f_closed'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'commit_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
        )
    ),

    "project_bug_types" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_128, 'notnull'=>true),
            'color'			=> array('type'=>SchemaProperty::TYPE_CHAR_6),
            'sort_order'	=> array('type'=>SchemaProperty::TYPE_SMALLINT, 'default'=>'0'),
            'commit_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
        )
    ),

    "project_bugs" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'related_bug'	=> array('type'=>SchemaProperty::TYPE_INT),
            'status_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'severity_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'owner_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'type_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'project_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'customer_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'title'			=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'description'	=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'solution'		=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'date_reported'	=> array('type'=>SchemaProperty::TYPE_DATE),
            'created_by'	=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'ts_entered'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'ts_updated'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'f_deleted'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'revision'		=> array('type'=>SchemaProperty::TYPE_INT),
            'uname'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'path'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'related_bug'	=> array("FKEY", "related_bug", "project_bugs", "id"),
            'status_id'		=> array("FKEY", "status_id", "project_bug_status", "id"),
            'severity_id'	=> array("FKEY", "severity_id", "project_bug_severity", "id"),
            'owner_id'		=> array("FKEY", "owner_id", "users", "id"),
            'type_id'		=> array("FKEY", "type_id", "project_bug_types", "id"),
            'customer_id'	=> array("FKEY", "customer_id", "customers", "id"),
        )
    ),

    "project_bug_types" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_128, 'notnull'=>true),
            'color'			=> array('type'=>SchemaProperty::TYPE_CHAR_6),
            'sort_order'	=> array('type'=>SchemaProperty::TYPE_SMALLINT, 'default'=>'0'),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
        )
    ),

    "project_priorities" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_128, 'notnull'=>true),
            'color'			=> array('type'=>SchemaProperty::TYPE_CHAR_6),
            'sort_order'	=> array('type'=>SchemaProperty::TYPE_SMALLINT, 'default'=>'0'),
            'commit_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
        )
    ),

    "project_templates" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'notes'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'time_created'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'custom_fields'	=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'user_id'		=> array("FKEY", "user_id", "users", "id"),
        )
    ),

    "project_template_members" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_INT, 'default'=>'auto_increment'),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'template_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'accepted'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'user_id'		=> array("FKEY", "user_id", "users", "id"),
            'template_id'	=> array("FKEY", "template_id", "project_templates", "id"),
        )
    ),

    "project_template_share" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_INT, 'default'=>'auto_increment'),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'template_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'accepted'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'user_id'		=> array("FKEY", "user_id", "users", "id"),
            'template_id'	=> array("FKEY", "template_id", "project_templates", "id"),
        )
    ),

    "project_template_tasks" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'notes'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'template_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'start_interval'=> array('type'=>SchemaProperty::TYPE_INT),
            'due_interval'	=> array('type'=>SchemaProperty::TYPE_INT),
            'start_count'	=> array('type'=>SchemaProperty::TYPE_INT),
            'due_count'		=> array('type'=>SchemaProperty::TYPE_INT),
            'timeline'		=> array('type'=>SchemaProperty::TYPE_CHAR_32),
            'type'			=> array('type'=>SchemaProperty::TYPE_INT),
            'file_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'position_id'	=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'timeline_date_begin'=> array('type'=>SchemaProperty::TYPE_CHAR_32),
            'timeline_date_due'=> array('type'=>SchemaProperty::TYPE_CHAR_32),
            'cost_estimated'=> array('type'=>SchemaProperty::TYPE_NUMERIC),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'user_id'		=> array("FKEY", "user_id", "users", "id"),
            'template_id'	=> array("FKEY", "template_id", "project_templates", "id"),
            'position_id'	=> array("FKEY", "position_id", "project_positions", "id"),
        )
    ),

    "projects" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'parent'		=> array('type'=>SchemaProperty::TYPE_INT),
            'priority'		=> array('type'=>SchemaProperty::TYPE_INT),
            'customer_id'	=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'template_id'	=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'notes'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'news'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'date_deadline'	=> array('type'=>SchemaProperty::TYPE_DATE),
            'date_completed'=> array('type'=>SchemaProperty::TYPE_DATE),
            'ts_created'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'ts_updated'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'f_deleted'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'revision'		=> array('type'=>SchemaProperty::TYPE_INT),
            'uname'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'path'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'parent'		=> array("FKEY", "parent", "projects", "id"),
            'priority'		=> array("FKEY", "priority", "project_priorities", "id"),
            'customer_id'	=> array("FKEY", "customer_id", "customers", "id"),
            'user_id'		=> array("FKEY", "user_id", "users", "id"),
            'template_id'	=> array("FKEY", "template_id", "project_templates", "id"),
            'date_completed'=> array("INDEX", "date_completed"),
        )
    ),

    "project_files" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_INT, 'default'=>'auto_increment'),
            'file_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'project_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'bug_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'task_id'		=> array('type'=>SchemaProperty::TYPE_INT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'project_id'	=> array("INDEX", "project_id", "projects", "id"),
            'project_id'	=> array("INDEX", "project_id", "projects", "id"),
            'bug_id'		=> array("INDEX", "bug_id", "project_bugs", "id"),
            'task_id'		=> array("INDEX", "task_id", "project_tasks", "id"),
        )
    ),

    "project_groups" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_INT, 'default'=>'auto_increment'),
            'parent_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_128, 'notnull'=>true),
            'color'			=> array('type'=>SchemaProperty::TYPE_CHAR_6),
            'sort_order'	=> array('type'=>SchemaProperty::TYPE_SMALLINT, 'default'=>'0'),
            'commit_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
        )
    ),

    "project_group_mem" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'project_id'	=> array('type'=>SchemaProperty::TYPE_BIGINT, 'notnull'=>true),
            'group_id'		=> array('type'=>SchemaProperty::TYPE_INT, 'notnull'=>true),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'project_id'	=> array("INDEX", "project_id", "projects", "id"),
            'group_id'		=> array("INDEX", "group_id", "project_groups", "id"),
        )
    ),

    "project_positions" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'project_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'template_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'commit_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'project_id'	=> array("INDEX", "project_id", "projects", "id"),
            'template_id'	=> array("INDEX", "template_id", "project_templates", "id"),
        )
    ),

    "project_membership" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'project_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'position_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'title'			=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'notes'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'accepted'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'invite_by'		=> array('type'=>SchemaProperty::TYPE_CHAR_128),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'user_id'		=> array("INDEX", "user_id", "users", "id"),
            'project_id'	=> array("INDEX", "project_id", "projects", "id"),
            'position_id'	=> array("INDEX", "position_id", "project_positions", "id"),
        )
    ),

    "project_milestones" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'project_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'position_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'notes'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'deadline'		=> array('type'=>SchemaProperty::TYPE_DATE),
            'f_completed'	=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'date_completed'=> array('type'=>SchemaProperty::TYPE_DATE),
            'creator_name'	=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'creator_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'ts_created'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'ts_updated'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'f_deleted'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'revision'		=> array('type'=>SchemaProperty::TYPE_INT),
            'uname'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'path'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'project_id'	=> array("FKEY", "project_id", "projects", "id"),
            'position_id'	=> array("FKEY", "position_id", "project_positions", "id"),
            'user_id'		=> array("FKEY", "user_id", "users", "id"),
            'creator_id'	=> array("FKEY", "creator_id", "users", "id"),
        )
    ),

    "project_tasks" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'notes'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'done'			=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'date_entered'	=> array('type'=>SchemaProperty::TYPE_DATE),
            'date_completed'=> array('type'=>SchemaProperty::TYPE_DATE),
            'start_date'=> array('type'=>SchemaProperty::TYPE_DATE),
            'priority'		=> array('type'=>SchemaProperty::TYPE_INT),
            'project'		=> array('type'=>SchemaProperty::TYPE_INT),
            'entered_by'	=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'deadline'		=> array('type'=>SchemaProperty::TYPE_DATE),
            'cost_estimated'=> array('type'=>SchemaProperty::TYPE_NUMERIC),
            'cost_actual'	=> array('type'=>SchemaProperty::TYPE_NUMERIC),
            'type'			=> array('type'=>SchemaProperty::TYPE_INT),
            'customer_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'template_task_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'position_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'creator_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'milestone_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'depends_task_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'case_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'recurrence_pattern'=> array('type'=>SchemaProperty::TYPE_INT),
            'ts_created'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'ts_updated'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'f_deleted'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'revision'		=> array('type'=>SchemaProperty::TYPE_INT),
            'uname'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'path'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'user_id'		=> array("FKEY", "user_id", "users", "id"),
            'project'		=> array("FKEY", "project", "projects", "id"),
            'customer_id'	=> array("FKEY", "customer_id", "customers", "id"),
            'position_id'	=> array("FKEY", "position_id", "project_positions", "id"),
            'creator_id'	=> array("FKEY", "creator_id", "users", "id"),
            'milestone_id'	=> array("FKEY", "milestone_id", "project_milestones", "id"),
            'depends_task_id'=> array("FKEY", "depends_task_id", "project_tasks", "id"),
            'date_entered'	=> array("INDEX", "date_entered"),
            'deadline'		=> array("INDEX", "deadline"),
        )
    ),

    "project_time" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'notes'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'owner_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'creator_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'task_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'date_applied'	=> array('type'=>SchemaProperty::TYPE_DATE),
            'hours'			=> array('type'=>SchemaProperty::TYPE_NUMERIC),
            'ts_created'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'ts_updated'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'f_deleted'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'revision'		=> array('type'=>SchemaProperty::TYPE_INT),
            'uname'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'path'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'owner_id'		=> array("FKEY", "owner_id", "users", "id"),
            'creator_id'	=> array("FKEY", "creator_id", "users", "id"),
            'task_id'		=> array("FKEY", "task_id", "project_tasks", "id"),
        )
    ),

    "reports" => array(
        "PROPERTIES" => array(
            'id'			    => array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			    => array('type'=>SchemaProperty::TYPE_CHAR_512),
            'description'	    => array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'obj_type'		    => array('type'=>SchemaProperty::TYPE_CHAR_256),
            'chart_type'	    => array('type'=>SchemaProperty::TYPE_CHAR_128),
            'chart_measure'	    => array('type'=>SchemaProperty::TYPE_CHAR_256),
            'chart_measure_agg'	=> array('type'=>SchemaProperty::TYPE_CHAR_32),
            'chart_dim1'	    => array('type'=>SchemaProperty::TYPE_CHAR_256),
            'chart_dim1_grp'	=> array('type'=>SchemaProperty::TYPE_CHAR_32),
            'chart_dim2'	    => array('type'=>SchemaProperty::TYPE_CHAR_256),
            'chart_dim2_grp'	=> array('type'=>SchemaProperty::TYPE_CHAR_32),
            'chart_type'	    => array('type'=>SchemaProperty::TYPE_CHAR_128),
            'chart_type'	    => array('type'=>SchemaProperty::TYPE_CHAR_128),
            'f_display_table'   => array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"true"),
            'f_display_chart'   => array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"true"),
            'scope'			    => array('type'=>SchemaProperty::TYPE_CHAR_32),
            'owner_id'		    => array('type'=>SchemaProperty::TYPE_INT),
            'custom_report'	    => array('type'=>SchemaProperty::TYPE_CHAR_512),
            'dataware_cube'	    => array('type'=>SchemaProperty::TYPE_CHAR_512),
            'ts_created'	    => array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'ts_updated'	    => array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'f_deleted'		    => array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'revision'		    => array('type'=>SchemaProperty::TYPE_INT),
            'uname'			    => array('type'=>SchemaProperty::TYPE_CHAR_256),
            'path'              => array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'table_type'        => array('type'=>SchemaProperty::TYPE_CHAR_32),
            'f_row_totals'      => array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'f_column_totals'   => array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'f_sub_totals'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'owner_id'		=> array("FKEY", "owner_id", "users", "id"),
        )
    ),

    "sales_orders" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'number'		=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'created_by'	=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'tax_rate'		=> array('type'=>SchemaProperty::TYPE_NUMERIC),
            'amount'		=> array('type'=>SchemaProperty::TYPE_NUMERIC),
            'ship_to'		=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'ship_to_cship'	=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'owner_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'status_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'customer_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'invoice_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'ts_entered'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'ts_updated'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'f_deleted'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'revision'		=> array('type'=>SchemaProperty::TYPE_INT),
            'uname'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'path'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'owner_id'		=> array("FKEY", "owner_id", "users", "id"),
            'status_id'		=> array("FKEY", "status_id", "sales_order_status", "id"),
            'customer_id'	=> array("FKEY", "customer_id", "customers", "id"),
            'invoice_id'	=> array("FKEY", "invoice_id", "customer_invoices", "id"),
        )
    ),

    "sales_order_detail" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'order_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'product_id'	=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'quantity'		=> array('type'=>SchemaProperty::TYPE_NUMERIC),
            'amount'		=> array('type'=>SchemaProperty::TYPE_NUMERIC),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'order_id'		=> array("INDEX", "order_id", "sales_orders", "id"),
            'product_id'	=> array("INDEX", "product_id", "products", "id"),
        )
    ),

    "sales_order_status" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_INT, 'default'=>'auto_increment'),
            'parent_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_128, 'notnull'=>true),
            'color'			=> array('type'=>SchemaProperty::TYPE_CHAR_6),
            'sort_order'	=> array('type'=>SchemaProperty::TYPE_SMALLINT, 'default'=>'0'),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
        )
    ),

    "security_dacl" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_512),
            'inherit_from'	=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'inherit_from_old'	=> array('type'=>SchemaProperty::TYPE_BIGINT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'name'			=> array("INDEX", "name"),
            'inherit_from'	=> array("INDEX", "inherit_from"),
            'inherit_from_old'	=> array("INDEX", "inherit_from"),
        )
    ),

    "security_acle" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'pname'			=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'dacl_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'group_id'		=> array('type'=>SchemaProperty::TYPE_INT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'user'			=> array("INDEX", "user_id", "users", "id"),
            'group'			=> array("INDEX", "group_id", "user_groups", "id"),
            'dacl'			=> array("INDEX", "dacl_id", "security_dacl", "id"),
        )
    ),

    "stocks" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_INT, 'default'=>'auto_increment'),
            'symbol'		=> array('type'=>SchemaProperty::TYPE_CHAR_32),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'price'			=> array('type'=>SchemaProperty::TYPE_CHAR_32),
            'price_change'	=> array('type'=>SchemaProperty::TYPE_CHAR_32),
            'percent_change'=> array('type'=>SchemaProperty::TYPE_CHAR_32),
            'last_updated'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'name'			=> array("INDEX", "name"),
            'symbol'		=> array("INDEX", "symbol"),
        )
    ),

    "stocks_membership" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'stock_id'		=> array('type'=>SchemaProperty::TYPE_INT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'user'			=> array("INDEX", "user_id", "users", "id"),
            'stock'			=> array("INDEX", "stock_id", "stocks", "id"),
        )
    ),

    "system_registry" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGINT, 'subtype'=>'', 'default'=>'auto_increment'),
            'key_name'		=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'key_val'		=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_INT),
        ),
        'PRIMARY_KEY' => 'id',
        "INDEXES" => array(
            array('properties'=>array("user_id")),
            array('properties'=>array("key_name", "user_id"), 'type'=>'UNIQUE'),
        )
    ),

    "user_dashboard_layout" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGINT, 'subtype'=>'', 'default'=>'auto_increment'),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'col'			=> array('type'=>SchemaProperty::TYPE_INT),
            'position'		=> array('type'=>SchemaProperty::TYPE_INT),
            'widget_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'type'			=> array('type'=>SchemaProperty::TYPE_CHAR_32, 'default'=>'system'),
            'data'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'dashboard'		=> array('type'=>SchemaProperty::TYPE_CHAR_128),
        ),
        'PRIMARY_KEY'	=> 'id',
        "KEYS" => array(
            'user_id' 		=> array("INDEX", "user_id", "users", "id"),
            'dashboard' 	=> array("INDEX", "dashboard"),
        )
    ),

    /**
     * User dictionary is used for spell checking
     */
    "user_dictionary" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_INT, 'default'=>'auto_increment'),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'word'			=> array('type'=>SchemaProperty::TYPE_CHAR_128),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'user_id' 		=> array("INDEX", "user_id", "users", "id"),
            'word' 			=> array("INDEX", "word"),
        )
    ),

    "user_teams" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_INT, 'subtype'=>'', 'default'=>'auto_increment'),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_256, 'notnull'=>true),
            'parent_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'commit_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
        ),
        'PRIMARY_KEY' => 'id',
        "KEYS" => array(
            'parent_id' 	=> array("INDEX", "parent_id"),
        )
    ),

    "user_timezones" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_INT, 'subtype'=>'', 'default'=>'auto_increment'),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_64, 'notnull'=>true),
            'code'			=> array('type'=>SchemaProperty::TYPE_CHAR_8, 'notnull'=>true),
            'loc_name'		=> array('type'=>SchemaProperty::TYPE_CHAR_64),
            'offs'			=> array('type'=>SchemaProperty::TYPE_REAL),
        ),
        'PRIMARY_KEY' => 'id',
        "KEYS" => array(
            'code'		 	=> array("INDEX", "code"),
        )
    ),

    "users" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'password'		=> array('type'=>SchemaProperty::TYPE_CHAR_32),
            'full_name'		=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'title'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'last_login'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'theme'			=> array('type'=>SchemaProperty::TYPE_CHAR_32),
            'timezone'		=> array('type'=>SchemaProperty::TYPE_CHAR_64),
            'country_code'	=> array('type'=>SchemaProperty::TYPE_CHAR_2),
            'active'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"true"),
            'phone'			=> array('type'=>SchemaProperty::TYPE_CHAR_32),
            'checkin_timestamp'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'active_timestamp'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP), // this might be the same as above...
            'status_text'	=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'quota_size'	=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'last_login_from'=> array('type'=>SchemaProperty::TYPE_CHAR_32),
            'image_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'team_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'manager_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'customer_number'=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'ts_updated'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'f_deleted'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'revision'		=> array('type'=>SchemaProperty::TYPE_INT),
            'uname'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'path'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "INDEXES" => array(
            array('properties'=>array("name", "password", "active")),
            array('properties'=>array("name"), 'type'=>"UNIQUE"),
        )
    ),

    "user_groups" => array(
        "PROPERTIES" => array(
            'id'		=> array('type'=>SchemaProperty::TYPE_INT, 'subtype'=>'', 'default'=>'auto_increment'),
            'name'		=> array('type'=>SchemaProperty::TYPE_CHAR_512, 'notnull'=>true),
            'f_system'	=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'f_admin'	=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'commit_id'	=> array('type'=>SchemaProperty::TYPE_BIGINT),
        ),
        'PRIMARY_KEY'	=> 'id',
        "KEYS" => array(
            'name' 	=> array("INDEX", "name"),
        )
    ),

    "user_group_mem" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'group_id'		=> array('type'=>SchemaProperty::TYPE_INT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'user'			=> array("INDEX", "user_id", "users", "id"),
            'group'			=> array("INDEX", "group_id", "user_groups", "id"),
        )
    ),

    "user_notes" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'body'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'date_added'	=> array('type'=>SchemaProperty::TYPE_DATE),
            'body_type'		=> array('type'=>SchemaProperty::TYPE_CHAR_32),
            'ts_entered'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'ts_updated'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'f_deleted'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'revision'		=> array('type'=>SchemaProperty::TYPE_INT),
            'uname'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'path'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'user_id'		=> array("FKEY", "owner_id", "users", "id"),
        )
    ),

    "user_notes_categories" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_INT, 'default'=>'auto_increment'),
            'parent_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_128, 'notnull'=>true),
            'color'			=> array('type'=>SchemaProperty::TYPE_CHAR_6),
            'sort_order'	=> array('type'=>SchemaProperty::TYPE_SMALLINT, 'default'=>'0'),
            'commit_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'parent_id'		=> array("INDEX", "parent_id"),
            'user_id'		=> array("INDEX", "user_id", "users", "id"),
        )
    ),

    "user_notes_cat_mem" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'note_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'category_id'	=> array('type'=>SchemaProperty::TYPE_INT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'note_id'		=> array("INDEX", "note_id"),
            'category_id'	=> array("INDEX", "category_id"),
        )
    ),

    "worker_jobs" => array(
        "PROPERTIES" => array(
            'job_id'		=> array('type'=>SchemaProperty::TYPE_CHAR_512, 'notnull'=>true),
            'function_name'	=> array('type'=>SchemaProperty::TYPE_CHAR_512),
            'ts_started'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'ts_completed'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'status_numerator'=> array('type'=>SchemaProperty::TYPE_INT, 'default'=>'-1'),
            'status_denominator'=> array('type'=>SchemaProperty::TYPE_INT, 'default'=>'100'),
            'log'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'retval'		=> array('type'=>SchemaProperty::TYPE_BINARY_STRING),
        ),
        'PRIMARY_KEY'=> 'job_id',
        "KEYS" => array(
        )
    ),

    "workflows" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_INT, 'default'=>'auto_increment'),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'notes'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'object_type'	=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'f_on_create'	=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'f_on_update'	=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'f_on_delete'	=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'f_singleton'	=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'f_allow_manual'=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'f_active'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'f_on_daily'    => array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'f_condition_unmet'    => array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'f_processed_cond_met'	=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),

            /*
             * This column is being depreciated with the V2 version of WorkFlow
             * and we will be using ts_lastrun below instead.
             */
            'ts_on_daily_lastrun'=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),

            /*
             * When the workflow was last run. This is particularly useful
             * for keeping track of 'periodic' workflows that run at an interval
             * and look for all matching entities.
             */
            'ts_lastrun'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'uname'         => array('type'=>SchemaProperty::TYPE_CHAR_256),
            'revision'		=> array('type'=>SchemaProperty::TYPE_INT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'obj_and_active'=> array("INDEX", array("object_type", "f_active")),
            'unique_name'=> array("INDEX", "uname"),
        )
    ),

    "workflow_actions" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'when_interval'	=> array('type'=>SchemaProperty::TYPE_SMALLINT),
            'when_unit'		=> array('type'=>SchemaProperty::TYPE_SMALLINT),
            'send_email_fid'=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'update_field'	=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'update_to'		=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'create_object'	=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'start_wfid'	=> array('type'=>SchemaProperty::TYPE_INT),
            'stop_wfid'		=> array('type'=>SchemaProperty::TYPE_INT),
            'workflow_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'type'			=> array('type'=>SchemaProperty::TYPE_SMALLINT),
            'type_name'		=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'parent_action_id'=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'parent_action_event'=> array('type'=>SchemaProperty::TYPE_CHAR_32),
            'uname'         => array('type'=>SchemaProperty::TYPE_CHAR_256),
            'data'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'workflow_id'	=> array("INDEX", "workflow_id", "workflows", "id"),
            'start_wfid'	=> array("INDEX", "start_wfid", "workflows", "id"),
            'stop_wfid'		=> array("INDEX", "stop_wfid", "workflows", "id"),
            'parent_action_id'=> array("INDEX", "parent_action_id"),
            'unique_name'	=> array("INDEX", "uname"),
        )
    ),

    "workflow_action_schedule" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'action_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'ts_execute'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'instance_id'	=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'inprogress'	=> array('type'=>SchemaProperty::TYPE_INT, 'default'=>'0'),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'ts_execute'	=> array("INDEX", "ts_execute"),
        )
    ),

    "workflow_conditions" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'blogic'		=> array('type'=>SchemaProperty::TYPE_CHAR_64),
            'field_name'	=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'operator'		=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'cond_value'	=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'workflow_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'wf_action_id'	=> array('type'=>SchemaProperty::TYPE_BIGINT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'workflow'		=> array("INDEX", "workflow_id", "workflows", "id"),
            'action'		=> array("INDEX", "wf_action_id", "workflow_actions", "id"),
        )
    ),

    "workflow_instances" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'object_type_id'=> array('type'=>SchemaProperty::TYPE_INT, 'notnull'=>true),
            'object_type'	=> array('type'=>SchemaProperty::TYPE_CHAR_128),
            'object_uid'	=> array('type'=>SchemaProperty::TYPE_BIGINT, 'notnull'=>true),
            'workflow_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'ts_started'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'ts_completed'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'f_completed'	=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'object_type_id'=> array("INDEX", "object_type_id", "app_object_types", "id"),
            'object_type'	=> array("INDEX", "object_type"),
            'object_uid'	=> array("INDEX", "object_uid"),
            'workflow'		=> array("INDEX", "workflow_id", "workflows", "id"),
        )
    ),

    "workflow_object_values" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'field'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'value'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'f_array'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'parent_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'action_id'		=> array('type'=>SchemaProperty::TYPE_INT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'parent_id'		=> array("INDEX", "parent_id"),
            'action_id'		=> array("INDEX", "action_id", "workflow_actions", "id"),
        )
    ),

    "workflow_approvals" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'notes'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'workflow_action_id'=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'status'		=> array('type'=>SchemaProperty::TYPE_CHAR_32),
            'requested_by'	=> array('type'=>SchemaProperty::TYPE_INT),
            'owner_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'obj_reference'	=> array('type'=>SchemaProperty::TYPE_CHAR_512),
            'ts_status_change'=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'ts_entered'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'ts_updated'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'f_deleted'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'revision'		=> array('type'=>SchemaProperty::TYPE_INT),
            'uname'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'path'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'owner_id'		=> array("FKEY", "owner_id", "users", "id"),
            'requested_by'	=> array("FKEY", "requested_by", "users", "id"),
            'workflow_action_id'=> array("FKEY", "workflow_action_id", "workflow_actions", "id"),
        )
    ),

    "xml_feeds" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'title'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'sort_by'		=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'limit_num'		=> array('type'=>SchemaProperty::TYPE_CHAR_8),
            'ts_created'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'ts_updated'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'f_deleted'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'revision'		=> array('type'=>SchemaProperty::TYPE_INT),
            'uname'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'path'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'user_id'		=> array("FKEY", "user_id", "users", "id"),
        )
    ),

    "xml_feed_groups" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_INT, 'default'=>'auto_increment'),
            'parent_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_128, 'notnull'=>true),
            'color'			=> array('type'=>SchemaProperty::TYPE_CHAR_6),
            'sort_order'	=> array('type'=>SchemaProperty::TYPE_SMALLINT, 'default'=>'0'),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'parent_id'		=> array("INDEX", "parent_id"),
        )
    ),

    "xml_feed_group_mem" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'feed_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'group_id'		=> array('type'=>SchemaProperty::TYPE_INT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'feed_id'		=> array("INDEX", "feed_id"),
            'group_id'		=> array("INDEX", "group_id"),
        )
    ),

    "xml_feed_publish" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_INT, 'default'=>'auto_increment'),
            'feed_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'publish_to'	=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'furl'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'feed_id'		=> array("INDEX", "feed_id", "xml_feeds", "id"),
        )
    ),

    "xml_feed_posts" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'time_entered'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'title'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'data'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'feed_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'f_publish'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'time_expires'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'user_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'ts_updated'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'f_deleted'		=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'revision'		=> array('type'=>SchemaProperty::TYPE_INT),
            'uname'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'path'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'user_id'		=> array("FKEY", "user_id", "users", "id"),
            'feed_id'		=> array("FKEY", "feed_id", "xml_feeds", "id"),
        )
    ),

    "xml_feed_post_categories" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_INT, 'default'=>'auto_increment'),
            'parent_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_128, 'notnull'=>true),
            'color'			=> array('type'=>SchemaProperty::TYPE_CHAR_6),
            'sort_order'	=> array('type'=>SchemaProperty::TYPE_SMALLINT, 'default'=>'0'),
            'feed_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'commit_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'parent_id'		=> array("INDEX", "parent_id"),
            'feed_id'		=> array("INDEX", "feed_id", "xml_feeds", "id"),
        )
    ),

    "xml_feed_post_cat_mem" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'post_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'category_id'	=> array('type'=>SchemaProperty::TYPE_INT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'post_id'		=> array("INDEX", "post_id", "xml_feed_posts", "id"),
            'category_id'	=> array("INDEX", "category_id", "xml_feed_post_categories", "id"),
        )
    ),

    "report_filters" => array(
        "PROPERTIES" => array(
            'id'            => array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'report_id'     => array('type'=>SchemaProperty::TYPE_BIGINT),
            'blogic'        => array("type"=>SchemaProperty::TYPE_CHAR_64, "notnull"=>true),
            'field_name'    => array("type"=>SchemaProperty::TYPE_CHAR_256, "notnull"=>true),
            'operator'      => array("type"=>SchemaProperty::TYPE_CHAR_128, "notnull"=>true),
            'value'         => array("type"=>SchemaProperty::TYPE_CHAR_TEXT),
        ),
        'PRIMARY_KEY'        => 'id',
        "KEYS" => array(
            'report_id'        => array("FKEY", "report_id", "reports", "id"),
        )
    ),

    "report_table_dims" => array(
        "PROPERTIES" => array(
            'id'            => array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'report_id'     => array('type'=>SchemaProperty::TYPE_BIGINT),
            'table_type'    => array("type"=>"character varying(32)", "notnull"=>true),
            'name'          => array("type"=>SchemaProperty::TYPE_CHAR_256, "notnull"=>true),
            'sort'          => array("type"=>"character varying(32)", "notnull"=>true),
            'format'        => array("type"=>"character varying(32)", "notnull"=>true),
            'f_column'      => array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'f_row'         => array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
        ),
        'PRIMARY_KEY'        => 'id',
        "KEYS" => array(
            'report_id'        => array("FKEY", "report_id", "reports", "id"),
        )
    ),

    "report_table_measures" => array(
        "PROPERTIES" => array(
            'id'            => array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'report_id'     => array('type'=>SchemaProperty::TYPE_BIGINT),
            'table_type'    => array("type"=>"character varying(32)", "notnull"=>true),
            'name'          => array("type"=>SchemaProperty::TYPE_CHAR_256, "notnull"=>true),
            'aggregate'     => array("type"=>"character varying(32)", "notnull"=>true),
        ),
        'PRIMARY_KEY'        => 'id',
        "KEYS" => array(
            'report_id'        => array("FKEY", "report_id", "reports", "id"),
        )
    ),

    "dashboard" => array(
        "PROPERTIES" => array(
            'id'            => array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'          => array('type'=>SchemaProperty::TYPE_CHAR_256),
            'description'   => array("type"=>SchemaProperty::TYPE_CHAR_TEXT),
            'scope'         => array("type"=>"character varying(32)", "notnull"=>true),
            'groups'        => array("type"=>SchemaProperty::TYPE_CHAR_TEXT),
            'owner_id'      => array("type"=>"integer"),
            'f_deleted'     => array("type"=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'path'          => array("type"=>SchemaProperty::TYPE_CHAR_TEXT),
            'payout'        => array("type"=>SchemaProperty::TYPE_CHAR_TEXT),
            'revision'      => array("type"=>"integer"),
            'uname'         => array('type'=>SchemaProperty::TYPE_CHAR_256),
            'ts_entered'    => array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'ts_updated'    => array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'dacl'			=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
        ),
        'PRIMARY_KEY'       => 'id',
        "KEYS" => array(
            'owner_id'      => array("FKEY", "owner_id", "users", "id"),
        )
    ),

    "dashboard_widgets" => array(
        "PROPERTIES" => array(
            'id'            => array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'dashboard_id'  => array("type"=>"integer"),
            'widget_id'     => array("type"=>"integer"),
            'widget'         => array('type'=>SchemaProperty::TYPE_CHAR_256),
            'col'           => array("type"=>"integer"),
            'pos'           => array("type"=>"integer"),
            'data'          => array("type"=>SchemaProperty::TYPE_CHAR_TEXT),
        ),
        'PRIMARY_KEY'       => 'id',
        "KEYS" => array(
            'dashboard_id'  => array("FKEY", "dashboard_id", "dashboard", "id"),
            'widget'		=> array("INDEX", "widget"),
        )
    ),

    "dataware_olap_cubes" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_512),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'uname'			=> array("INDEX", "name"),
        )
    ),

    "dataware_olap_cube_dims" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'type'			=> array('type'=>SchemaProperty::TYPE_CHAR_32),
            'cube_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'cube'		=> array("FKEY", "cube_id", "dataware_olap_cubes", "id"),
        )
    ),

    "dataware_olap_cube_measures" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'name'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'cube_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'cube'		=> array("FKEY", "cube_id", "dataware_olap_cubes", "id"),
        )
    ),

    /**
     * Store history of commit heads
     */
    "object_sync_commit_heads" => array(
        "PROPERTIES" => array(
            'type_key'		=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'head_commit_id'=> array('type'=>SchemaProperty::TYPE_BIGINT, 'notnull'=>true),
        ),
        'PRIMARY_KEY'		=> 'type_key',
        "KEYS" => array(
        )
    ),

    "object_sync_partners" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'pid'			=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'owner_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'ts_last_sync'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'owner_id'		=> array("FKEY", "owner_id", "users", "id"),
            'partid'		=> array("INDEX", "pid"),
        )
    ),

    "object_sync_partner_collections" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'type'			=> array('type'=>SchemaProperty::TYPE_INT),
            'partner_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'object_type_id'=> array('type'=>SchemaProperty::TYPE_INT),
            'object_type'	=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'field_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'field_name'	=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'ts_last_sync'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
            'conditions'	=> array('type'=>SchemaProperty::TYPE_CHAR_TEXT),
            'f_initialized'	=> array('type'=>SchemaProperty::TYPE_BOOL, "default"=>"false"),
            'revision'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'last_commit_id'=> array('type'=>SchemaProperty::TYPE_BIGINT),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'partner_id'	=> array("FKEY", "partner_id", "object_sync_partners", "id"),
            'field_id'		=> array("FKEY", "field_id", "app_object_type_fields", "id"),
            'object_type_id'=> array("FKEY", "object_type_id", "app_object_types", "id"),
        )
    ),

    "object_sync_partner_collection_init" => array(
        "PROPERTIES" => array(
            'collection_id'	=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'parent_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'ts_completed'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
        ),
        "KEYS" => array(
            'collection'	=> array("FKEY", "collection_id", "object_sync_partner_collections", "id"),
            'parent'		=> array("INDEX", "parent_id"),
        )
    ),


    "object_sync_stats" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'collection_id'	=> array('type'=>SchemaProperty::TYPE_INT),
            'object_type_id'=> array('type'=>SchemaProperty::TYPE_INT),
            'object_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'parent_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'field_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            'revision'		=> array('type'=>SchemaProperty::TYPE_INT),
            'field_name'	=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'field_val'		=> array('type'=>SchemaProperty::TYPE_CHAR_256),
            'action'		=> array('type'=>SchemaProperty::TYPE_CHAR),
            'ts_entered'	=> array('type'=>SchemaProperty::TYPE_TIMESTAMP),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'collection'	=> array("FKEY", "collection_id", "object_sync_partner_collections", "id"),
            'object_type_id'=> array("FKEY", "object_type_id", "app_object_types", "id"),
            'object'		=> array("INDEX", array("object_type_id", "object_id")),
            'fval'			=> array("INDEX", array("field_id", "field_val")),
            'tsentered'		=> array("INDEX", "ts_entered"),
        )
    ),

    "object_sync_import" => array(
        "PROPERTIES" => array(
            'id'			=> array('type'=>SchemaProperty::TYPE_BIGSERIAL),
            'collection_id'	=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'object_type_id'=> array('type'=>SchemaProperty::TYPE_INT),
            // Local object id once imported
            'object_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
            // Revision of the local object
            'revision'		=> array('type'=>SchemaProperty::TYPE_INT),
            // This field is depricated and should eventually be deleted
            'parent_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
            // This field is depricated and should eventually be deleted
            'field_id'		=> array('type'=>SchemaProperty::TYPE_INT),
            // A revision (usually modified epoch) of the remote object
            'remote_revision' => array('type'=>SchemaProperty::TYPE_INT),
            // The unique id of the remote object we have imported
            'unique_id'		=> array('type'=>SchemaProperty::TYPE_CHAR_512),
        ),
        'PRIMARY_KEY'		=> 'id',
        "KEYS" => array(
            'collection'	=> array("FKEY", "collection_id", "object_sync_partner_collections", "id"),
            'object_type_id'=> array("FKEY", "object_type_id", "app_object_types", "id"),
            'field_id'		=> array("FKEY", "field_id", "app_object_type_fields", "id"),
            'object'		=> array("INDEX", array("object_type_id", "object_id")),
            'unique_id'		=> array("INDEX", array("field_id", "unique_id")),
            'parent_id'		=> array("INDEX", "parent_id"),
        )
    ),

    "object_sync_export" => array(
        "PROPERTIES" => array(
            'collection_id'	=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'collection_type' => array('type'=>SchemaProperty::TYPE_SMALLINT),
            'commit_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'new_commit_id'	=> array('type'=>SchemaProperty::TYPE_BIGINT),
            'unique_id'		=> array('type'=>SchemaProperty::TYPE_BIGINT),
        ),
        "KEYS" => array(
            'collection'	=> array("FKEY", "collection_id", "object_sync_partner_collections", "id"),
            'collecttionid'	=> array("INDEX", "collection_id"),
            'unique_id'		=> array("INDEX", "unique_id"),
            'new_commit_id'	=> array("INDEX", "new_commit_id", "new_commit_id IS NOT NULL"),
            'commituni'		=> array("INDEX", array("collection_type", "commit_id")),
            'newcommituni'	=> array("INDEX", array("collection_type", "new_commit_id")),
        )
    ),
);