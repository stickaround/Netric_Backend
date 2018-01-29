<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return array(
    'is_private' => false,
    'default_activity_level' => 1,
    'store_revisions' => false,
    'fields' => array(
        'name' => array(
            'title'=>'Name',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>false,
            'required'=>true,
        ),

        'notes' => array(
            'title'=>'Notes',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>false
        ),

        'closed_lost_reson' => array(
            'title'=>'Closed Lost Reason',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>false
        ),

        'expected_close_date' => array(
            'title'=>'Exp. Close Date',
            'type'=>Field::TYPE_DATE,
            'subtype'=>'',
            'readonly'=>false
        ),

        'amount' => array(
            'title'=>'Est. Amount',
            'type'=>Field::TYPE_NUMBER,
            'subtype'=>'number',
            'readonly'=>false,
        ),

        'ts_closed' => array(
            'title'=>'Time Closed',
            'type'=>Field::TYPE_TIMESTAMP,
            'subtype'=>'',
            'readonly'=>false
        ),

        'probability_per' => array(
            'title'=>'Est. Probability %',
            'type'=>Field::TYPE_INTEGER,
            'subtype'=>'',
            'readonly'=>false,
            'optional_values'=>array(
                "10"=>"10%", "25"=>"25%", "50"=>"50%", "75"=>"75%", "90"=>"90%", "100"=>"100%"
            ),
        ),

        'f_closed' => array(
            'title'=>'Closed',
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
            'readonly'=>false,
            'default'=>array("value"=>"f", "on"=>"null"),
        ),

        'f_won' => array(
            'title'=>'Won',
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
            'readonly'=>false,
            'default'=>array("value"=>"f", "on"=>"null"),
        ),

        // Marketing campaign references
        'campaign_id' => array(
            'title'=>'Campaign',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'marketing_campaign'
        ),

        'owner_id' => array(
            'title'=>'Owner',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'user',
            'default'=>array("value"=>"-3", "on"=>"null"),
        ),

        'stage_id' => array(
            'title'=>'Stage',
            'type'=>Field::TYPE_GROUPING,
            'subtype'=>'object_groupings',
            'fkey_table'=>array("key"=>"id", "title"=>"name")
        ),

        'customer_id' => array(
            'title'=>'Contact',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'customer',
            'required'=>true
        ),

        'lead_id' => array(
            'title'=>'Lead',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'lead'
        ),

        'lead_source_id' => array(
            'title'=>'Source',
            'type'=>Field::TYPE_GROUPING,
            'subtype'=>'object_groupings',
            'fkey_table'=>array("key"=>"id", "title"=>"name")
        ),
        'type_id' => array(
            'title'=>'Type',
            'type'=>Field::TYPE_GROUPING,
            'subtype'=>'object_groupings',
            'fkey_table'=>array("key"=>"id", "title"=>"name")
        ),
        'objection_id' => array(
            'title'=>'Objection',
            'type'=>Field::TYPE_GROUPING,
            'subtype'=>'object_groupings',
            'fkey_table'=>array("key"=>"id", "title"=>"name")
        ),
        'selling_point_id' => array(
            'title'=>'Selling Point',
            'type'=>Field::TYPE_GROUPING,
            'subtype'=>'object_groupings',
            'fkey_table'=>array("key"=>"id", "title"=>"name", "parent"=>"parent_id",
                "ref_table"=>array(
                    "table"=>"object_grouping_mem",
                    "this"=>"object_id",
                    "ref"=>"grouping_id"
                ),
            ),
        ),
        'folder_id' => array(
            'title'=>'Files',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'folder',
            'autocreate'=>true, // Create foreign object automatically
            'autocreatebase'=>'/System/Customer Files/Opportunities', // Where to create
            'autocreatename'=>'id', // the field to pull the new object name from
            'fkey_table'=>array("key"=>"id", "title"=>"name")
        ),
    ),
);
