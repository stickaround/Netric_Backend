<?php
namespace data\entity_definitions;

return array(
    'revision' => 4,
    'is_private' => true,
    'default_activity_level' => 1,
    'store_revisions' => true,
    'fields' => array(
        'name' => array(
            'title'=>'Name',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>false,
            'require'=>true
        ),
        'parent_id' => array(
            'title'=>'Parent',
            'type'=>'integer',
            'readonly'=>false,
            'require'=>false
        ),
    ),
);
