<?php
namespace data\entity_definitions;

use Netric\EntityDefinition\Field;
use Netric\Entity\ObjType\UserEntity;

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
            'title' => 'Publish Number',
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
        'groups' => array(
            'title' => 'Groups',
            'type'=>Field::TYPE_GROUPING_MULTI,
            'subtype' => 'object_groupings',
        ),
        'site_id' => array(
            'title' => 'Site',
            'type'=>Field::TYPE_OBJECT,
            'subtype' => 'cms_site',
            'readonly' => false,
        ),
    ),
);
