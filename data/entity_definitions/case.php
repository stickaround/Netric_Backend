<?php
namespace data\entity_definitions;

return array(
    'revision' => 39,
    'inherit_dacl_ref' => 'project_id',
    'fields' => array(
        'title' => array(
            'title'=>'Title',
            'type'=>'text',
            'subtype'=>'128',
            'readonly'=>false
        ),
        'description' => array(
            'title'=>'Description',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>false
        ),
        'created_by' => array(
            'title'=>'Entered By',
            'type'=>'text',
            'subtype'=>'128',
            'readonly'=>true,
            'default'=>array(
                "value"=>"<%username%>",
                "on"=>"create"
            ),
        ),
        'notify_email' => array(
            'title'=>'Notify Email',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>true
        ),
        'date_reported' => array(
            'title'=>'Date Entered',
            'type'=>'date',
            'subtype'=>'',
            'readonly'=>true,
            'default'=>array("value"=>"now", "on"=>"create"),
        ),
        'related_bug' => array(
            'title'=>'Related Case',
            'type'=>'object',
            'subtype'=>'case',
        ),
        'status_id' => array(
            'title'=>'Status',
            'type'=>'fkey',
            'subtype'=>'project_bug_status',
            'fkey_table'=>array("key"=>"id", "title"=>"name")
        ),
        'severity_id' => array(
            'title'=>'Severity',
            'type'=>'fkey',
            'subtype'=>'project_bug_severity',
            'fkey_table'=>array("key"=>"id", "title"=>"name"),
        ),
        'owner_id' => array(
            'title'=>'Owner',
            'type'=>'object',
            'subtype'=>'user',
            'default'=>array("value"=>"-3", "on"=>"null"),
        ),
        'project_id' => array(
            'title'=>'Project',
            'type'=>'object',
            'subtype'=>'project'
        ),
        'type_id' => array(
            'title'=>'Type',
            'type'=>'fkey',
            'subtype'=>'project_bug_types',
            'fkey_table'=>array("key"=>"id", "title"=>"name")
        ),
        'customer_id' => array(
            'title'=>'Contact',
            'type'=>'object',
            'subtype'=>'customer'
        ),
    ),
);
