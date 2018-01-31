<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return array(
    'fields' => array(
        "name" => array(
            'title'=>'Title',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false,
            "required"=>true,
        ),

        "notes" => array(
            'title'=>'Details',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>false,
        ),

        "user_name" => array(
            'title'=>'User Name',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'128',
            'readonly'=>true,
        ),

        "f_readonly" => array(
            'title'=>'Read Only',
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
            'readonly'=>true,
        ),

        "f_private" => array(
            'title' => 'Private',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => true,
            'default' => array(
                "value"=>'f',
                "on"=>"null",
            ),
        ),

        "direction" => array(
            'title'=>'Direction',
            'type'=>Field::TYPE_TEXT,
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
            'type'=>Field::TYPE_NUMBER,
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
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'',
            'readonly'=>true,
        ),

        // Who/what did the action
        'subject' => array(
            'title'=>'Subject',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'',
            'readonly'=>true,
        ),

        // What the action was done to
        'obj_reference' => array(
            'title'=>'Reference',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'',
            'readonly'=>true,
        ),

        // File attachments
        'attachments' => array(
            'title'=>'Attachments',
            'type'=>Field::TYPE_OBJECT_MULTI,
            'subtype'=>'file',
        ),

        'user_id' => array('title'=>'User',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'user',
            'default'=>array(
                "value"=>"-3",
                "on"=>"null"
            ),
        ),

        'type_id' => array(
            'title'=>'Type',
            'type'=>Field::TYPE_GROUPING,
            'subtype'=>'object_groupings',
            "required"=>true
        ),
    ),
    'store_revisions' => false,
);
