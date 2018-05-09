<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return array(
    'inherit_dacl_ref' => 'project_id',
    'fields' => array(
        'title' => array(
            'title'=>'Title',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'128',
            'readonly'=>false
        ),
        'description' => array(
            'title'=>'Description',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>false
        ),
        'created_by' => array(
            'title'=>'Entered By',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'128',
            'readonly'=>true,
            'default'=>array(
                "value"=>"<%username%>",
                "on"=>"create"
            ),
        ),
        'notify_email' => array(
            'title'=>'Notify Email(s)',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>true
        ),
        'date_reported' => array(
            'title'=>'Date Entered',
            'type'=>Field::TYPE_DATE,
            'subtype'=>'',
            'readonly'=>true,
            'default'=>array("value"=>"now", "on"=>"create"),
        ),
        'related_bug' => array(
            'title'=>'Related Case',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'case',
        ),
        'status_id' => array(
            'title'=>'Status',
            'type'=>Field::TYPE_GROUPING,
            'subtype'=>'object_groupings',
        ),
        'severity_id' => array(
            'title'=>'Severity',
            'type'=>Field::TYPE_GROUPING,
            'subtype'=>'object_groupings',
        ),
        'owner_id' => array(
            'title'=>'Owner',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'user',
            'default'=>array("value"=>"-3", "on"=>"null"),
        ),
        'project_id' => array(
            'title'=>'Project',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'project'
        ),
        'type_id' => array(
            'title'=>'Type',
            'type'=>Field::TYPE_GROUPING,
            'subtype'=>'object_groupings',
        ),
        'customer_id' => array(
            'title'=>'Contact',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'customer'
        ),
    ),
);
