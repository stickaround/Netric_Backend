<?php

namespace data\entity_definitions;

use Netric\EntityDefinition\Field;

return array(
    'parent_field' => 'folder_id',
    'fields' => array(
        'name' => array(
            'title' => 'Name',
            'type' => Field::TYPE_TEXT,
            'subtype' => '512',
            'readonly' => false,
        ),

        // Size in bytes
        'file_size' => array(
            'title' => 'Size',
            'type' => Field::TYPE_NUMBER,
            'subtype' => '',
            'readonly' => true,
        ),

        // The filetype extension
        'filetype' => array(
            'title' => 'Name',
            'type' => Field::TYPE_TEXT,
            'subtype' => '32',
            'readonly' => true,
        ),

        // where the file is stored in the storage engine
        'storage_path' => array(
            'title' => 'Storage Path',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => true,
        ),

        // Deprecated - path to local file on server
        'dat_local_path' => array(
            'title' => 'Lcl Path',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => true,
        ),

        // Deprecated - key used on ANS server
        'dat_ans_key' => array(
            'title' => 'ANS Key',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => true,
        ),
        'folder_id' => array(
            'title' => 'Folder',
            'type' => Field::TYPE_OBJECT,
            'subtype' => 'folder'
        ),
    ),
);
