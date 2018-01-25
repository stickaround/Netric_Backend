<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return array(
    'fields' => array(
        'name' => array(
            'title'=>'Title',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'128',
            'readonly'=>false
        ),

        'data' => array(
            'title'=>'Body', 'type'=>'text', 'subtype'=>'', 'readonly'=>false
        ),

        // Posts can be linked to sites
        "site_id" => array(
            'title'=>'Site',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'cms_site',
            'readonly'=>false,
        ),

        // Snippets can also be linked to pages
        "page_id" => array(
            'title'=>'Page',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'cms_page',
            'readonly'=>false,
        ),
    )
);
