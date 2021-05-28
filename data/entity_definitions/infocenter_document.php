<?php

namespace data\entity_definitions;

use Netric\EntityDefinition\Field;
use Netric\EntityDefinition\ObjectTypes;

return [
    'parent_field' => 'parent_document',
    'fields' => [
        'title' => [
            'title' => 'Title',
            'type' => Field::TYPE_TEXT,
            'subtype' => '512',
            'readonly' => false
        ],
        'keywords' => [
            'title' => 'Keywords',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => false
        ],
        'author_name' => [
            'title' => 'Author Name',
            'type' => Field::TYPE_TEXT,
            'subtype' => '64',
            'readonly' => false
        ],
        'body' => [
            'title' => 'Body',
            'type' => Field::TYPE_TEXT,
            'subtype' => 'html',
            'readonly' => false
        ],
        'rating' => [
            'title' => 'Rating',
            'type' => Field::TYPE_NUMBER,
            'subtype' => 'integer',
            'readonly' => false
        ],
        'video_file_id' => [
            'title' => 'Video File',
            'type' => Field::TYPE_OBJECT,
            'subtype' => 'file',
            'fkey_table' => ["key" => "id", "title" => "file_title"]
        ],
        'groups' => [
            'title' => 'Groups',
            'type' => Field::TYPE_GROUPING_MULTI,
            'subtype' => 'object_groupings',
        ],
        'parent_document' => [
            'title' => "Parent",
            'type' => Field::TYPE_OBJECT,
            'subtype' => ObjectTypes::DOCUMENT,
        ],
        'is_rootspace' => [
            'title' => 'Is Rootspace',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false,
        ],
    ],
];
