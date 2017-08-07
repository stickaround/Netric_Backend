<?php
namespace data\entity_definitions;

return array(
    'revision' => 29,
    'is_private' => false,
    'default_activity_level' => 1,
    'store_revisions' => false,
    'fields' => array(
        'name' => array(
            'title'=>'Name',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>false,
            'required'=>true,
        ),

        'notes' => array(
            'title'=>'Notes',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>false
        ),

        'closed_lost_reson' => array(
            'title'=>'Closed Lost Reason',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>false
        ),

        'expected_close_date' => array(
            'title'=>'Exp. Close Date',
            'type'=>'date',
            'subtype'=>'',
            'readonly'=>false
        ),

        'amount' => array(
            'title'=>'Est. Amount',
            'type'=>'number',
            'subtype'=>'number',
            'readonly'=>false,
        ),

        'ts_closed' => array(
            'title'=>'Time Closed',
            'type'=>'timestamp',
            'subtype'=>'',
            'readonly'=>false
        ),

        'probability_per' => array(
            'title'=>'Est. Probability %',
            'type'=>'integer',
            'subtype'=>'',
            'readonly'=>false,
            'optional_values'=>array(
                "10"=>"10%", "25"=>"25%", "50"=>"50%", "75"=>"75%", "90"=>"90%", "100"=>"100%"
            ),
        ),

        'f_closed' => array(
            'title'=>'Closed',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>false,
            'default'=>array("value"=>"f", "on"=>"null"),
        ),

        'f_won' => array(
            'title'=>'Won',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>false,
            'default'=>array("value"=>"f", "on"=>"null"),
        ),

        // Marketing campaign references
        'campaign_id' => array(
            'title'=>'Campaign',
            'type'=>'object',
            'subtype'=>'marketing_campaign'
        ),

        'owner_id' => array(
            'title'=>'Owner',
            'type'=>'object',
            'subtype'=>'user',
            'default'=>array("value"=>"-3", "on"=>"null"),
        ),

        'stage_id' => array(
            'title'=>'Stage',
            'type'=>'fkey',
            'subtype'=>'customer_opportunity_stages',
            'fkey_table'=>array("key"=>"id", "title"=>"name")
        ),

        'customer_id' => array(
            'title'=>'Contact',
            'type'=>'object',
            'subtype'=>'customer',
            'required'=>true
        ),

        'lead_id' => array(
            'title'=>'Lead',
            'type'=>'object',
            'subtype'=>'lead'
        ),

        'lead_source_id' => array(
            'title'=>'Source',
            'type'=>'fkey',
            'subtype'=>'customer_lead_sources',
            'fkey_table'=>array("key"=>"id", "title"=>"name")
        ),
        'type_id' => array(
            'title'=>'Type',
            'type'=>'fkey',
            'subtype'=>'customer_opportunity_types',
            'fkey_table'=>array("key"=>"id", "title"=>"name")
        ),
        'objection_id' => array(
            'title'=>'Objection',
            'type'=>'fkey',
            'subtype'=>'customer_objections',
            'fkey_table'=>array("key"=>"id", "title"=>"name")
        ),
        'selling_point_id' => array(
            'title'=>'Selling Point',
            'type'=>'fkey',
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
            'type'=>'object',
            'subtype'=>'folder',
            'autocreate'=>true, // Create foreign object automatically
            'autocreatebase'=>'/System/Customer Files/Opportunities', // Where to create
            'autocreatename'=>'id', // the field to pull the new object name from
            'fkey_table'=>array("key"=>"id", "title"=>"name")
        ),
    ),
);
