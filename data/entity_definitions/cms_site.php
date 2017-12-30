<?php
namespace data\entity_definitions;

return array(
    'fields' => array(
        // Textual name
        'name' => array(
            'title'=>'Name',
            'type'=>'text',
            'subtype'=>'512',
            'readonly'=>false,
        ),

        // The production URL
        'url' => array(
            'title'=>'URL',
            'type'=>'text',
            'subtype'=>'512',
            'readonly'=>false,
        ),

        // The testing URL
        'url_test' => array(
            'title'=>'TEST URL',
            'type'=>'text',
            'subtype'=>'512',
            'readonly'=>false,
        ),

        // Media folder
        'folder_id' => array(
            'title'=>'Media',
            'type'=>'object',
            'subtype'=>'folder',
            'readonly'=>false,
            'autocreate'=>true, // Create foreign object automatically
            'autocreatebase'=>'/System/Objects/cms_site', // Where to create (for folders, the path with no trail slash)
            'autocreatename'=>'id', // the field to pull the new object name from
        ),

        "owner_id" => array(
            'title'=>'Owner',
            'type'=>'object',
            'subtype'=>'user',
            'default'=>array(
                "value"=>"-3",
                "on"=>"null"
            ),
        ),
    ),
);
