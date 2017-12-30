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

        // Posts can be linked to sites
        "site_id" => array(
            'title'=>'Site',
            'type'=>'object',
            'subtype'=>'cms_site',
            'readonly'=>false,
        ),
    ),
);
