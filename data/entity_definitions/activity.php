<?php
namespace data\entity_definitions;

return array(
    'fields' => array(
        "name" => array(
            'title'=>'Title',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>false,
            "required"=>true,
        ),

        "notes" => array(
            'title'=>'Details',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>false,
        ),

        "user_name" => array(
            'title'=>'User Name',
            'type'=>'text',
            'subtype'=>'128',
            'readonly'=>true,
        ),

        "f_readonly" => array(
            'title'=>'Read Only',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>true,
        ),

        "f_private" => array(
            'title' => 'Private',
            'type' => 'bool',
            'subtype' => '',
            'readonly' => true,
            'default' => array(
                "value"=>'f',
                "on"=>"null",
            ),
        ),

        "direction" => array(
            'title'=>'Direction',
            'type'=>'text',
            'subtype'=>'1',
            'readonly'=>false,
            'optional_values'=>array(
                "n"=>"None",
                "i"=>"Incoming",
                "o"=>"Outgoing",
            ),
        ),

        "level" => array(
            'title'=>'Level',
            'type'=>'number',
            'subtype'=>'integer',
            'readonly'=>true,
            'default'=>array(
                "value"=>"3",
                "on"=>"null",
            ),
        ),

        // What action was done
        "verb" => array(
            'title' => 'Action',
            'type' => 'text',
            'subtype' => '32',
            'readonly' => true,
            'default' => array(
                "value"=>'create',
                "on"=>"null",
            ),
        ),

        // Optional reference to object that was used to perform the action/verb
        'verb_object' => array(
            'title'=>'Origin',
            'type'=>'object',
            'subtype'=>'',
            'readonly'=>true,
        ),

        // Who/what did the action
        'subject' => array(
            'title'=>'Subject',
            'type'=>'object',
            'subtype'=>'',
            'readonly'=>true,
        ),

        // What the action was done to
        'obj_reference' => array(
            'title'=>'Reference',
            'type'=>'object',
            'subtype'=>'',
            'readonly'=>true,
        ),

        // File attachments
        'attachments' => array(
            'title'=>'Attachments',
            'type'=>'object_multi',
            'subtype'=>'file',
        ),

        'user_id' => array('title'=>'User',
            'type'=>'object',
            'subtype'=>'user',
            'default'=>array(
                "value"=>"-3",
                "on"=>"null"
            ),
        ),

        'type_id' => array(
            'title'=>'Type',
            'type'=>'fkey',
            'subtype'=>'activity_types',
            'fkey_table'=>array(
                "key"=>"id",
                "title"=>"name"
            ),
            "required"=>true
        ),
    ),
    'store_revisions' => false,
);
