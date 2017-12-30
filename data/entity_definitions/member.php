<?php
namespace data\entity_definitions;

return array(
    'parent_field' => 'parent_id',
    'fields' => array(
        'name' => array(
            'title'=>'Member',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>false
        ),
        'role' => array(
            'title'=>'Role',
            'type'=>'text',
            'subtype'=>'512',
            'readonly'=>false
        ),
        'f_invsent' => array(
            'title'=>'Inv. Sent',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>false
        ),
        'f_accepted' => array(
            'title'=>'Accepted',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>false
        ),
        'f_required' => array(
            'title'=>'Required',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>false
        ),
        'obj_member' => array(
            'title'=>'Member',
            'type'=>'object',
            'subtype'=>'',
            'readonly'=>true
        ),
        'obj_reference' => array(
            'title'=>'Reference',
            'type'=>'object',
            'subtype'=>'',
            'readonly'=>true
        ),
    ),
);
