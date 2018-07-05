<?php
namespace data\entity_definitions;

use Netric\EntityDefinition\Field;
use Netric\Entity\ObjType\UserEntity;

return array(
    "id" => array(
        'title' => "Local ID",
        'type' => Field::TYPE_NUMBER,
        'id' => "0",
        'subtype' => "",
        'readonly' => true,
        'system' => true,
    ),
    "guid" => array(
        'title' => "Global ID",
        'type' => Field::TYPE_UUID,
        'id' => "0",
        'subtype' => "",
        'readonly' => true,
        'system' => true,
    ),
    'associations' => array(
        'title' => 'Associations',
        'type' => Field::TYPE_OBJECT_MULTI,
        'subtype' => '',
        'readonly' => true,
        'system' => true,
    ),
    'attachments' => array(
        'title' => 'Attachments',
        'type' => Field::TYPE_OBJECT_MULTI,
        'subtype' => 'file',
        'readonly' => true,
        'system' => true,
    ),
    'followers' => array(
        'title' => 'Followers',
        'type' => Field::TYPE_OBJECT_MULTI,
        'subtype' => 'user',
        'readonly' => true,
        'system' => true,
    ),
    'activity' => array(
        'title' => 'Activity',
        'type' => Field::TYPE_OBJECT_MULTI,
        'subtype' => 'activity',
        'system' => true,
    ),
    'comments' => array(
        'title' => 'Comments',
        'type' => Field::TYPE_OBJECT_MULTI,
        'subtype' => 'comment',
        'readonly' => false,
        'system' => true,
    ),
    'num_comments' => array(
        'title' => 'Num Comments',
        'type' => 'number',
        'subtype' => Field::TYPE_INTEGER,
        'readonly' => true,
        'system' => true,
    ),
    'commit_id' => array(
        'title' => 'Commit Revision',
        'type' => Field::TYPE_NUMBER,
        'subtype' => '',
        'readonly' => true,
        'system' => true,
    ),
    'f_deleted' => array(
        'title' => 'Deleted',
        'type' => Field::TYPE_BOOL,
        'subtype' => '',
        'readonly' => true,
        'system' => true,
    ),
    'owner_id' => array(
        'title'=>'Assigned To',
        'type'=>Field::TYPE_OBJECT,
        'subtype'=>'user',
        'default'=>array("value"=>UserEntity::USER_CURRENT, "on"=>"null")
    ),
    'creator_id' => array(
        'title'=>'Creator',
        'type'=>Field::TYPE_OBJECT,
        'subtype'=>'user',
        'readonly'=>true,
        'default'=>array("value"=>UserEntity::USER_CURRENT, "on"=>"null")
    ),

    // Default is true on null for this so not every entity is marked as unseen (annoying)
    'f_seen' => array(
        'title' => 'Seen',
        'type' => Field::TYPE_BOOL,
        'subtype' => '',
        'readonly' => true,
        'system' => true,
        'default' => array(
            "value" => true,
            "on" => "null"
        ),
    ),
    'revision' => array(
        'title' => 'Revision',
        'type' => Field::TYPE_NUMBER,
        'subtype' => '',
        'readonly' => true,
        'system' => true,
    ),

    // The full path based on parent objects
    // DEPRICATED: appears to no longer be used, but maybe we should start
    // because searches would be a lot easier in the future.
    'path' => array(
        'title' => 'Path',
        'type' => Field::TYPE_TEXT,
        'subtype' => '',
        'readonly' => true,
        'system' => true,
    ),

    // Unique name in URL escaped form if object type uses it, otherwise the id
    'uname' => array(
        'title' => 'Uname',
        'type' => Field::TYPE_TEXT,
        'subtype' => '256',
        'readonly' => true,
        'system' => true,
    ),
    'dacl' => array(
        'title' => 'Security',
        'type' => Field::TYPE_TEXT,
        'subtype' => '',
        'readonly' => true,
        'system' => true,
    ),
    'ts_entered' => array(
        'title' => 'Time Entered',
        'type' => Field::TYPE_TIMESTAMP,
        'subtype' => '',
        'readonly' => true,
        'system' => true,
        'default' => array(
            "value" => "now",
            "on" => "create"
        ),
    ),
    'ts_updated' => array(
        'title' => 'Time Last Changed',
        'type' => Field::TYPE_TIMESTAMP,
        'subtype' => '',
        'readonly' => true,
        'system' => true,
        'default' => array(
            "value" => "now",
            "on" => "update"
        ),
    ),
);