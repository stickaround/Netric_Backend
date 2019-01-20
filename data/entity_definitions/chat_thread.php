<?php
namespace data\entity_definitions;

use Netric\EntityDefinition\Field;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Entity\ObjType\UserEntity;

return [
    'default_activity_level' => 1,
    'store_revisions' => false,
    'fields' => [
        /*
         * A global unique identifier to pull chats together across multiple threads if needed
         * depending on how many users are involved. This is generally just the guid of the
         * first thread created by the originating user.
         */
        "conversation_guid" => [
            'title' => "Conversation ID",
            'type' => Field::TYPE_UUID,
            'subtype' => "",
            'readonly' => true,
            'system' => true,
        ],
        'subject' => [
            'title'=>'Subject',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>true,
        ],
        'scope' => [
            'title'=>'Scope',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'32',
            'optional_values'=>["system"=>"Channel", "user"=>"Private"]
        ],
        'last_message_body' => [
            'title'=>'Last Message',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>true,
        ],
        'participants' => [
            'title'=>'People',
            'type'=>Field::TYPE_OBJECT_MULTI,
            'subtype'=>ObjectTypes::USER,
            'readonly'=>true,
            'default' => ["value" => UserEntity::USER_CURRENT, "on" => "create"]
        ],
        'num_attachments' => [
            'title'=>'Num Attachments',
            'type'=>Field::TYPE_INTEGER,
            'subtype'=>'',
            'readonly'=>true,
            "default"=>["value"=>"0", "on"=>"null"]
        ],
        'num_messages' => [
            'title'=>'Num Messages',
            'type'=>Field::TYPE_INTEGER,
            'subtype'=>'',
            'readonly'=>true
        ],
        'f_seen' => [
            'title'=>'Seen',
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
            'readonly'=>false
        ],
        'f_flagged' => [
            'title'=>'Flagged',
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
            'readonly'=>false
        ],
    ],
];
