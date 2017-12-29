<?php
namespace data\entity_definitions;

return array(
    'revision' => 11,
    'fields' => array(
        'name' => array(
            'title'=>'Name',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>false
        ),
        'notes' => array(
            'title'=>'Notes',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>false
        ),
        'rating' => array(
            'title'=>'Rating',
            'type'=>'number',
            'subtype'=>'',
            'readonly'=>false
        ),
        'product' => array(
            'title'=>'Product',
            'type'=>'object',
            'subtype'=>'product',
            'readonly'=>false
        ),
        'creator_id' => array(
            'title'=>'Reviewer',
            'type'=>'object',
            'subtype'=>'user',
            'readonly'=>true,
            'default'=>array("value"=>"-3", "on"=>"null")
        ),
    ),
    'aggregates' => array(
        'avg_rating' => array(
            'type' => 'avg',
            'calc_field' => 'rating',
            'ref_obj_update' => 'product',
            'obj_field_to_update' => 'rating',
        ),
    ),
);
