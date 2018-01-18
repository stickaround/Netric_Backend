<?php
namespace data\entity_definitions;

use Netric\Entity\ObjType\UserEntity;

return array(
    'parent_field' => "parent",
    'child_dacls' => array("case", "task", "project_milestone"),
    'fields' => array(
        'name' => array(
            'title' => 'Title',
            'type' => 'text',
            'subtype' => '128',
            'readonly' => false
        ),
        'notes' => array(
            'title' => 'Description',
            'type' => 'text',
            'subtype' => '',
            'readonly' => false
        ),
        'news' => array(
            'title' => 'News',
            'type' => 'text',
            'subtype' => '',
            'readonly' => false
        ),
        'date_started' => array(
            'title' => 'Start Date',
            'type' => 'date',
            'subtype' => '',
            'readonly' => false,
            'default' => array("value" => "now", "on" => "create")
        ),
        'date_deadline' => array(
            'title' => 'Deadline',
            'type' => 'date',
            'subtype' => '',
            'readonly' => false
        ),
        'date_completed' => array(
            'title' => 'Completed',
            'type' => 'date',
            'subtype' => '',
            'readonly' => false
        ),
        'parent' => array(
            'title' => 'Parent',
            'type' => 'object',
            'subtype' => 'project',
            'fkey_table' => array("key" => "id", "title" => "name")
        ),
        'priority' => array(
            'title' => 'Priority',
            'type' => 'fkey',
            'subtype' => 'object_groupings',
            'fkey_table' => array("key" => "id", "title" => "name")
        ),
        'user_id' => array(
            'title' => 'Owner',
            'type' => 'object',
            'subtype' => 'user'
        ),
        'customer_id' => array(
            'title' => 'Contact',
            'type' => 'object',
            'subtype' => 'customer'
        ),
        'groups' => array(
            'title' => 'Groups',
            'type' => 'fkey_multi',
            'subtype' => 'object_groupings',
            'fkey_table' => array("key" => "id", "title" => "name", "parent" => "parent_id",
                "ref_table" => array(
                    "table" => "object_grouping_mem",
                    "this" => "object_id",
                    "ref" => "grouping_id"
                ),
            ),
        ),
        'members' => array(
            'title' => 'Members',
            'type' => 'object_multi',
            'subtype' => 'user',
            'default' => array("value" => UserEntity::USER_CURRENT, "on" => "create")
        ),
        'folder_id' => array(
            'type' => 'object',
            'subtype' => 'folder',
            'autocreate' => true, // Create foreign object automatically
            'autocreatebase' => '/System/Project Files', // Where to create (for folders, the path with no trail slash)
            'autocreatename' => 'id', // the field to pull the new object name from
            'fkey_table' => array("key" => "id", "title" => "name")
        ),
    ),
);
