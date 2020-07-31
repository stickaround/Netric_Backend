<?php

namespace data\entity_definitions;

use Netric\EntityDefinition\Field;
use Netric\Entity\ObjType\UserEntity;

return [
    "entity_id" => [
        'title' => "ID",
        'type' => Field::TYPE_UUID,
        'subtype' => "",
        'readonly' => true,
        'system' => true,
    ],
    "account_id" => [
        'title' => "Account ID",
        'type' => Field::TYPE_UUID,
        'subtype' => "",
        'readonly' => true,
        'system' => true,
    ],
    'associations' => [
        'title' => 'Associations',
        'type' => Field::TYPE_OBJECT_MULTI,
        'subtype' => '',
        'readonly' => true,
        'system' => true,
    ],
    'attachments' => [
        'title' => 'Attachments',
        'type' => Field::TYPE_OBJECT_MULTI,
        'subtype' => 'file',
        'readonly' => true,
        'system' => true,
    ],
    'followers' => [
        'title' => 'Followers',
        'type' => Field::TYPE_OBJECT_MULTI,
        'subtype' => 'user',
        'readonly' => true,
        'system' => true,
    ],
    'activity' => [
        'title' => 'Activity',
        'type' => Field::TYPE_OBJECT_MULTI,
        'subtype' => 'activity',
        'system' => true,
    ],
    'comments' => [
        'title' => 'Comments',
        'type' => Field::TYPE_OBJECT_MULTI,
        'subtype' => 'comment',
        'readonly' => false,
        'system' => true,
    ],
    'num_comments' => [
        'title' => 'Num Comments',
        'type' => 'number',
        'subtype' => Field::TYPE_INTEGER,
        'readonly' => true,
        'system' => true,
    ],
    'commit_id' => [
        'title' => 'Commit Revision',
        'type' => Field::TYPE_NUMBER,
        'subtype' => '',
        'readonly' => true,
        'system' => true,
    ],
    'f_deleted' => [
        'title' => 'Deleted',
        'type' => Field::TYPE_BOOL,
        'subtype' => '',
        'readonly' => true,
        'system' => true,
    ],
    'owner_id' => [
        'title' => 'Assigned To',
        'type' => Field::TYPE_OBJECT,
        'subtype' => 'user',
        'default' => ["value" => UserEntity::USER_CURRENT, "on" => "null"]
    ],
    'creator_id' => [
        'title' => 'Creator',
        'type' => Field::TYPE_OBJECT,
        'subtype' => 'user',
        'readonly' => true,
        'default' => ["value" => UserEntity::USER_CURRENT, "on" => "null"]
    ],

    // Default is true on null for this so not every entity is marked as unseen (annoying]
    'f_seen' => [
        'title' => 'Seen',
        'type' => Field::TYPE_BOOL,
        'subtype' => '',
        'readonly' => true,
        'system' => true,
        'default' => [
            "value" => true,
            "on" => "null"
        ],
    ],
    'revision' => [
        'title' => 'Revision',
        'type' => Field::TYPE_NUMBER,
        'subtype' => '',
        'readonly' => true,
        'system' => true,
    ],

    // The full path based on parent objects
    // DEPRICATED: appears to no longer be used, but maybe we should start
    // because searches would be a lot easier in the future.
    'path' => [
        'title' => 'Path',
        'type' => Field::TYPE_TEXT,
        'subtype' => '',
        'readonly' => true,
        'system' => true,
    ],

    // Unique name in URL escaped form if object type uses it, otherwise the id
    'uname' => [
        'title' => 'Uname',
        'type' => Field::TYPE_TEXT,
        'subtype' => '256',
        'readonly' => true,
        'system' => true,
    ],
    'dacl' => [
        'title' => 'Security',
        'type' => Field::TYPE_TEXT,
        'subtype' => '',
        'readonly' => true,
        'system' => true,
    ],
    'ts_entered' => [
        'title' => 'Time Entered',
        'type' => Field::TYPE_TIMESTAMP,
        'subtype' => '',
        'readonly' => true,
        'system' => true,
        'default' => [
            "value" => "now",
            "on" => "create"
        ],
    ],
    'ts_updated' => [
        'title' => 'Time Last Changed',
        'type' => Field::TYPE_TIMESTAMP,
        'subtype' => '',
        'readonly' => true,
        'system' => true,
        'default' => [
            "value" => "now",
            "on" => "update"
        ],
    ],
];
