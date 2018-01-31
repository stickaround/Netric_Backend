<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;
use Netric\Entity\ObjType\UserEntity;

return array(
    'parent_field' => "parent",
    'child_dacls' => array("case", "task", "project_milestone"),
    'fields' => array(
        'name' => array(
            'title' => 'Title',
            'type'=>Field::TYPE_TEXT,
            'subtype' => '128',
            'readonly' => false
        ),
        'notes' => array(
            'title' => 'Description',
            'type'=>Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => false
        ),
        'news' => array(
            'title' => 'News',
            'type'=>Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => false
        ),
        'date_started' => array(
            'title' => 'Start Date',
            'type'=>Field::TYPE_DATE,
            'subtype' => '',
            'readonly' => false,
            'default' => array("value" => "now", "on" => "create")
        ),
        'date_deadline' => array(
            'title' => 'Deadline',
            'type'=>Field::TYPE_DATE,
            'subtype' => '',
            'readonly' => false
        ),
        'date_completed' => array(
            'title' => 'Completed',
            'type'=>Field::TYPE_DATE,
            'subtype' => '',
            'readonly' => false
        ),
        'parent' => array(
            'title' => 'Parent',
            'type'=>Field::TYPE_OBJECT,
            'subtype' => 'project',
            'fkey_table' => array("key" => "id", "title" => "name")
        ),
        'priority' => array(
            'title' => 'Priority',
            'type'=>Field::TYPE_GROUPING,
            'subtype' => 'object_groupings',
        ),
        'user_id' => array(
            'title' => 'Owner',
            'type'=>Field::TYPE_OBJECT,
            'subtype' => 'user'
        ),
        'customer_id' => array(
            'title' => 'Contact',
            'type'=>Field::TYPE_OBJECT,
            'subtype' => 'customer'
        ),
        'groups' => array(
            'title' => 'Groups',
            'type'=>Field::TYPE_GROUPING_MULTI,
            'subtype' => 'object_groupings',
        ),
        'members' => array(
            'title' => 'Members',
            'type'=>Field::TYPE_OBJECT_MULTI,
            'subtype' => 'user',
            'default' => array("value" => UserEntity::USER_CURRENT, "on" => "create")
        ),
        'folder_id' => array(
            'type'=>Field::TYPE_OBJECT,
            'subtype' => 'folder',
            'autocreate' => true, // Create foreign object automatically
            'autocreatebase' => '/System/Project Files', // Where to create (for folders, the path with no trail slash)
            'autocreatename' => 'id', // the field to pull the new object name from
            'fkey_table' => array("key" => "id", "title" => "name")
        ),
    ),
);
