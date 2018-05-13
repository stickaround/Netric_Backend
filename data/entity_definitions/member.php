<?php
namespace data\entity_definitions;

use Netric\EntityDefinition\Field;
return array(
    'parent_field' => 'parent_id',
    'fields' => array(
        'name' => array(
            'title'=>'Member',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false
        ),
        'role' => array(
            'title'=>'Role',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'512',
            'readonly'=>false
        ),
        'f_invsent' => array(
            'title'=>'Invitation Sent',
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
            'readonly'=>false
        ),
        'f_accepted' => array(
            'title'=>'Accepted',
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
            'readonly'=>false
        ),
        'f_required' => array(
            'title'=>'Required',
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
            'readonly'=>false
        ),
        'obj_member' => array(
            'title'=>'Member',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'',
            'readonly'=>true
        ),
        'obj_reference' => array(
            'title'=>'Reference',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'',
            'readonly'=>true
        ),
    ),
);
