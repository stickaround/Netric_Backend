<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return array(
    'is_private' => true,
    'default_activity_level' => 1,
    'store_revisions' => true,
    'fields' => array(
        'name' => array(
            'title'=>'Name',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false,
            'require'=>true
        ),
        'parent_id' => array(
            'title'=>'Parent',
            'type'=>Field::TYPE_INTEGER,
            'readonly'=>false,
            'require'=>false,
            'default' => ["value" => 0, "on" => "create"]
        ),
    ),
);
