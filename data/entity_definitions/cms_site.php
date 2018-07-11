<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return array(
    'fields' => array(
        // Textual name
        'name' => array(
            'title'=>'Name',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'512',
            'readonly'=>false,
        ),

        // The production URL
        'url' => array(
            'title'=>'URL',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'512',
            'readonly'=>false,
        ),

        // The testing URL
        'url_test' => array(
            'title'=>'TEST URL',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'512',
            'readonly'=>false,
        ),

        // Media folder
        'folder_id' => array(
            'title'=>'Media',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'folder',
            'readonly'=>false,
            'autocreate'=>true, // Create foreign object automatically
            'autocreatebase'=>'/System/Objects/cms_site', // Where to create (for folders, the path with no trail slash)
            'autocreatename'=>'id', // the field to pull the new object name from
        ),
    ),
);
