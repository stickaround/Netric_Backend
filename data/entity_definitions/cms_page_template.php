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

        // Pages can be linked to sites
        "site_id" => array(
            'title'=>'Site',
            'type'=>'object',
            'subtype'=>'cms_site',
            'readonly'=>false,
        ),

        // Type : blank page, module
        'type' => array(
            'title'=>'Type',
            'type'=>'text',
            'subtype'=>'32',
            'optional_values'=>array("blank"=>"Blank Page", "module"=>"Module"),
        ),

        // Type : blank page, module
        'module' => array(
            'title'=>'Module Name',
            'type'=>'text',
            'subtype'=>'128',
        ),
    ),
);
