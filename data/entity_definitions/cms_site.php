<?php

namespace data\entity_definitions;

use Netric\EntityDefinition\Field;

return array(
    'fields' => array(
        // Textual name
        'name' => array(
            'title' => 'Name',
            'type' => Field::TYPE_TEXT,
            'subtype' => '512',
            'readonly' => false,
        ),

        // The production URL
        'url' => array(
            'title' => 'URL',
            'type' => Field::TYPE_TEXT,
            'subtype' => '512',
            'readonly' => false,
        ),

        // The testing URL
        'url_test' => array(
            'title' => 'TEST URL',
            'type' => Field::TYPE_TEXT,
            'subtype' => '512',
            'readonly' => false,
        ),
    ),
);
