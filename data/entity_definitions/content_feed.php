<?php
namespace data\entity_definitions;

return array(
    'list_title' => 'title',
    'fields' => array(
        'title' => array(
            'title' => 'Title',
            'type' => 'text',
            'subtype' => '256',
            'readonly' => false
        ),
        'description' => array(
            'title' => 'Description',
            'type' => 'text',
            'subtype' => '',
            'readonly' => false
        ),
        'sort_by' => array(
            'title' => 'Publish Sort',
            'type' => 'text',
            'subtype' => '128',
            'readonly' => false
        ),
        'limit_num' => array(
            'title' => 'Publish Num',
            'type' => 'text',
            'subtype' => '8', '
        readonly' => false
        ),
        'subs_title' => array(
            'title' => 'Subscribe Label',
            'type' => 'text',
            'subtype' => '256',
            'readonly' => false
        ),
        'subs_body' => array(
            'title' => 'Subscribe Body',
            'type' => 'text',
            'subtype' => '',
            'readonly' => false
        ),
        'user_id' => array(
            'title' => 'User',
            'type' => 'object',
            'subtype' => 'user',
            'readonly' => true,
            'default' => array("value" => "-3", "on" => "null")
        ),
        'groups' => array(
            'title' => 'Groups',
            'type' => 'fkey_multi',
            'subtype' => 'object_groupings',
        ),
        'site_id' => array(
            'title' => 'Site',
            'type' => 'object',
            'subtype' => 'cms_site',
            'readonly' => false,
        ),
    ),
);
