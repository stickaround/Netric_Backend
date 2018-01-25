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

        // Pages can be linked to sites
        "site_id" => array(
            'title'=>'Site',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'cms_site',
            'readonly'=>false,
        ),

        // Type : blank page, module
        'type' => array(
            'title'=>'Type',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'32',
            'optional_values'=>array("blank"=>"Blank Page", "module"=>"Module"),
        ),

        // Type : blank page, module
        'module' => array(
            'title'=>'Module Name',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'128',
        ),
    ),
);
