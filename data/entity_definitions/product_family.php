<?php
namespace data\entity_definitions;

return array(
    'revision' => 10,
    'fields' => array(
        'name' => array(
            'title'=>'Name',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>false
        ),
        'notes' => array(
            'title'=>'Notes',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>false
        ),
        'f_available' => array(
            'title'=>'Available',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>false,
            'default'=>array("value"=>"t", "on"=>"null")
        ),
        'image_id' => array(
            'title'=>'Image',
            'type'=>'object',
            'subtype'=>'file',
            'fkey_table'=>array("key"=>"id", "title"=>"file_title")
        ),
    ),
);
