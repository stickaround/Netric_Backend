<?php

namespace data\entity_definitions;

use Netric\EntityDefinition\Field;

return [
    'fields' => [
        "name" => [
            'title' => 'Title',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false,
            "required" => true,
        ],

        "notes" => [
            'title' => 'Details',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => false,
        ],

        // If activity should be private to the owner/creator
        // We really only do this if the entity being acted on is_private
        "is_private" => [
            'title' => 'Private',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => true,
            'default' => [
                "value" => 'f',
                "on" => "null",
            ],
        ],

        // Optional direction used for incoming and outgoing
        // actions like "email sent" or "call received"
        "direction" => [
            'title' => 'Direction',
            'type' => Field::TYPE_TEXT,
            'subtype' => '1',
            'readonly' => false,
            'optional_values' => [
                "n" => "None",
                "i" => "Incoming",
                "o" => "Outgoing",
            ],
        ],

        // Activity levels are used to filter out noise
        // Anything 3 or higher is generaly a direct change
        // by a user. Lower numbers are often indirect or system
        // changes (like setting a seen flag to true) that might not
        // be interesting in an activity feed.
        "level" => [
            'title' => 'Level',
            'type' => Field::TYPE_NUMBER,
            'subtype' => 'integer',
            'readonly' => true,
            'default' => [
                "value" => "3",
                "on" => "null",
            ],
        ],

        // What action was done
        // This will normally come from Netric\Entity\EntityEvents::EVENT_* constants
        "verb" => [
            'title' => 'Action',
            'type' => 'text',
            'subtype' => '32',
            'readonly' => true,
            'default' => [
                "value" => 'create',
                "on" => "null",
            ],
        ],

        // Optional reference to object that was used to perform the action/verb
        'verb_object' => [
            'title' => 'Origin',
            'type' => Field::TYPE_OBJECT,
            'subtype' => '',
            'readonly' => true,
        ],

        // The subject refers to the person or the thing that is acting
        // This will usually be a user id, and the same user
        // as the crator. But in special cases the actor might
        // be a workflow, or a contact if they sent a reply or message
        'subject' => [
            'title' => 'Subject',
            'type' => Field::TYPE_OBJECT,
            'subtype' => '',
            'readonly' => true,
        ],

        // The object that the action was done to
        'obj_reference' => [
            'title' => 'Reference',
            'type' => Field::TYPE_OBJECT,
            'subtype' => '',
            'readonly' => true,
        ],

        // File attachments
        'attachments' => [
            'title' => 'Attachments',
            'type' => Field::TYPE_OBJECT_MULTI,
            'subtype' => 'file',
        ],
    ],
    'store_revisions' => false,
];
