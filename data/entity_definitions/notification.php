<?php
namespace data\entity_definitions;

return array(
    'is_private' => true,
    'default_activity_level' => 1,
    'store_revisions' => false,
    'fields' => array(
        // Textual name or subject
        'name' => array(
            'title'=>'Title',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>false,
            'require'=>true,
        ),

        // Notification content text
        'description' => array(
            'title'=>'Description',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>false,
        ),

        // The object we are reminding on
        'obj_reference' => array(
            'title'=>'Concering',
            'type'=>'object',
            'subtype'=>'',
            'readonly'=>false,
        ),

        // Who this notification is sent to
        'owner_id' => array(
            'title'=>'Owner',
            'type'=>'object',
            'subtype'=>'user',
            'readonly'=>false,
            'require'=>true,
            'default'=>array(
                "on"=>"null",
                "value"=>"-3",
            ),
        ),

        // Who created this notification
        'creator_id' => array(
            'title'=>'Creator',
            'type'=>'object',
            'subtype'=>'user',
            'readonly'=>false,
            'require'=>true,
            'default'=>array(
                "on"=>"null",
                "value"=>"-3",
            ),
        ),

        // Flag indicating if the notification has been seen
        'f_seen' => array(
            'title'=>'Seen',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>false,
            'default'=>array(
                "on"=>"null",
                "value"=>"f",
            ),
        ),

        // Flag indicating if the notification has been showed already
        'f_shown' => array(
            'title'=>'Showed',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>false,
            'default'=>array(
                "on"=>"null",
                "value"=>false,
            ),
        ),

        // Flag indicating if the notification should be a popup
        'f_popup' => array(
            'title'=>'Popup Alert',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>false,
        ),

        // Flag indicating if the notification should be emailed
        'f_email' => array(
            'title'=>'Send Email',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>false,
        ),

        // Flag indicating if the notification should be text messaged
        'f_sms' => array(
            'title'=>'Send SMS',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>false,
        ),

        // The actual time when this reminder should execute
        'ts_execute' => array(
            'title'=>'Execute Time',
            'type'=>'timestamp',
            'subtype'=>'',
            'readonly'=>false,
            'default'=>array(
                "on"=>"null",
                "value"=>"now",
            ),
        ),
    ),
);
