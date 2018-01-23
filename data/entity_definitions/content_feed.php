<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return array(
    'list_title' => 'title',
    'fields' => array(
        'title' => array(
            'title' => 'Title',
            'type'=>Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false
        ),
        'description' => array(
            'title' => 'Description',
            'type'=>Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => false
        ),
        'sort_by' => array(
            'title' => 'Publish Sort',
            'type'=>Field::TYPE_TEXT,
            'subtype' => '128',
            'readonly' => false
        ),
        'limit_num' => array(
            'title' => 'Publish Num',
            'type'=>Field::TYPE_TEXT,
            'subtype' => '8',
            'readonly' => false
        ),
        'subs_title' => array(
            'title' => 'Subscribe Label',
            'type'=>Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false
        ),
        'subs_body' => array(
            'title' => 'Subscribe Body',
            'type'=>Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => false
        ),
        'user_id' => array(
            'title' => 'User',
            'type'=>Field::TYPE_OBJECT,
            'subtype' => 'user',
            'readonly' => true,
            'default' => array("value" => "-3", "on" => "null")
        ),
        'groups' => array(
            'title' => 'Groups',
            'type'=>Field::TYPE_GROUPING_MULTI,
            'subtype' => 'object_groupings',
            'fkey_table' => array(
                "key" => "id",
                "title" => "name",
                "parent" => "parent_id",
                "ref_table" => array(
                    "table" => "object_grouping_mem",
                    "this" => "object_id",
                    "ref" => "grouping_id"
                ),
            ),
        ),
        'site_id' => array(
            'title' => 'Site',
            'type'=>Field::TYPE_OBJECT,
            'subtype' => 'cms_site',
            'readonly' => false,
        ),
    ),
);
