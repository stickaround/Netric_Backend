<?php
namespace data\entity_definitions;

return array(
    'fields' => array(
        'name' => array(
            'title'=>'Subject',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>false
        ),
        'notes' => array(
            'title'=>'Details',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>false
        ),
        'workflow_action_id' => array(
            'title'=>'Workflow Action',
            'type'=>'integer',
            'subtype'=>'',
            'readonly'=>true
        ),
        'status' => array(
            'title'=>'Status',
            'type'=>'text',
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
            'type'=>'timestamp',
            'subtype'=>'',
            'readonly'=>true
        ),
        'requested_by' => array(
            'title'=>'Requested By',
            'type'=>'object',
            'subtype'=>'user',
            'readonly'=>true,
            'required'=>true,
            'default'=>array("value"=>"-3", "on"=>"null")
        ),
        'owner_id' => array(
            'title'=>'Owner',
            'type'=>'object',
            'subtype'=>'user',
            'readonly'=>false,
            'required'=>true
        ),
        'obj_reference' => array(
            'title'=>'Reference',
            'type'=>'object',
            'subtype'=>'',
            'readonly'=>true
        ),
    ),
    'default_activity_level' => 4,
);
