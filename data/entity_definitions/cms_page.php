<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return array(
    'parent_field' => 'parent_id',
    'uname_settings' => 'site_id:parent_id:name',
    'fields' => array(
        // Textual name
        'name' => array(
            'title'=>'Name',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'512',
            'readonly'=>false,
        ),

        'title' => array(
            'title'=>'Title',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'512',
            'readonly'=>false,
        ),

        'f_navmain' => array(
            'title'=>'Show in Main Nav',
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
            'readonly'=>false,
            'default'=>array("value"=>"f", "on"=>"null"),
        ),

        'f_publish' => array(
            'title'=>'Published',
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
            'readonly'=>false,
            'default'=>array("value"=>"f", "on"=>"null"),
        ),

        'meta_description' => array(
            'title'=>'Meta Description',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'150',
            'readonly'=>false
        ),

        'meta_keywords' => array(
            'title'=>'Meta Keywords',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'150',
            'readonly'=>false
        ),

        "time_publish" => array(
            'title'=>'Publish After',
            'type'=>Field::TYPE_TIMESTAMP,
            'subtype'=>'',
            'readonly'=>false
        ),

        "time_expires" => array(
            'title'=>'Expires',
            'type'=>Field::TYPE_TIMESTAMP,
            'subtype'=>'',
            'readonly'=>false
        ),

        // Pages can be linked to sites
        "site_id" => array(
            'title'=>'Site',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'cms_site',
            'readonly'=>false,
        ),

        // Pages can use a template
        "template_id" => array(
            'title'=>'Template',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'cms_page_template',
            'readonly'=>false,
        ),

        // Body
        'data' => array(
            'title'=>'Body',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>false,
        ),

        // Menu Order
        'sort_order' => array(
            'title'=>'Menu Order',
            'type'=>Field::TYPE_NUMBER,
            'subtype'=>'',
            'readonly'=>false,
        ),

        // The parent page
        "parent_id" => array(
            'title'=>'Parent',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'cms_page',
            'readonly'=>false,
        ),

        // Status flag
        'status_id' => array(
            'title'=>'Status',
            'type'=>Field::TYPE_GROUPING,
            'subtype'=>'object_groupings',
            'fkey_table'=>array(
                "key"=>"id",
                "title"=>"name"
            ),
        ),
    ),
);
