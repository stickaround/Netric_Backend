<?php
namespace data\entity_definitions;

return array(
    'fields' => array(
        'name' => array(
            'title'=>'Title',
            'type'=>'text',
            'subtype'=>'128',
            'readonly'=>false
        ),

        'data' => array(
            'title'=>'Body', 'type'=>'text', 'subtype'=>'', 'readonly'=>false
        ),

        // Posts can be linked to sites
        "site_id" => array(
            'title'=>'Site',
            'type'=>'object',
            'subtype'=>'cms_site',
            'readonly'=>false,
        ),

        // Snippets can also be linked to pages
        "page_id" => array(
            'title'=>'Page',
            'type'=>'object',
            'subtype'=>'cms_page',
            'readonly'=>false,
        ),
    )
);
