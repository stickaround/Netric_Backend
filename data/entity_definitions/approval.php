<?php
namespace data\entity_definitions;

use Netric\EntityDefinition\Field;
use Netric\Entity\ObjType\UserEntity;

return array(
    'fields' => array(
        'name' => array(
            'title'=>'Subject',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false
        ),
        'notes' => array(
            'title'=>'Details',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>false
        ),
        'workflow_action_id' => array(
            'title'=>'Workflow Action',
            'type'=>Field::TYPE_INTEGER,
            'subtype'=>'',
            'readonly'=>true
        ),
        'status' => array(
            'title'=>'Status',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'32',
            'readonly'=>true,
            'default'=>array("value"=>"awaiting", "on"=>"null"),
            'optional_values'=>array(
                "awaiting"=>"Awaiting Approval",
                "approved"=>"Approved",
                "declined"=>"Declined"
            ),
        ),
        'ts_status_change' => array(
            'title'=>'Time Status Changed',
            'type'=>Field::TYPE_TIMESTAMP,
            'subtype'=>'',
            'readonly'=>true
        ),
        'requested_by' => array(
            'title'=>'Requested By',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'user',
            'readonly'=>true,
            'required'=>true,
            'default'=>array("value"=>UserEntity::USER_CURRENT, "on"=>"null")
        ),
        'obj_reference' => array(
            'title'=>'Reference',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'',
            'readonly'=>true
        ),
    ),
    'default_activity_level' => 4,
);
