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
        'company_name' => array(
            'title'=>'Company Name',
            'type'=>'text',
            'subtype'=>'128',
            'readonly'=>false
        ),
        'company_slogan' => array(
            'title'=>'Company Slogan',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>false
        ),
        'notes_line1' => array(
            'title'=>'Notes - Line 1',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>false
        ),
        'notes_line2' => array(
            'title'=>'Notes - Line 2',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>false
        ),
        'footer_line1' => array(
            'title'=>'Footer',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>false
        ),
        'company_logo' => array(
            'title'=>'Logo',
            'type'=>'object',
            'subtype'=>'file',
            'fkey_table'=>array("key"=>"id", "title"=>"file_title")
        ),
        'owner_id' => array(
            'title'=>'Owner',
            'type'=>'object',
            'subtype'=>'user'
        ),
    ),
);
