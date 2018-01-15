<?php
namespace data\entity_definitions;

return array(
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
        'price' => array(
            'title'=>'Price',
            'type'=>'number',
            'subtype'=>'double precision',
            'readonly'=>false
        ),
        'f_available' => array(
            'title'=>'Available',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>false,
            'default'=>array("value"=>"t", "on"=>"null")
        ),
        'rating' => array(
            'title'=>'Rating',
            'type'=>'number',
            'subtype'=>'integer',
            'readonly'=>false
        ),
        'family' => array(
            'title'=>'Product Family',
            'type'=>'object',
            'subtype'=>'product_family',
            'readonly'=>false
        ),
        'categories' => array(
            'title'=>'Categories',
            'type'=>'fkey_multi',
            'subtype'=>'object_groupings',
            'fkey_table'=>array("key"=>"id", "title"=>"name", "parent"=>"parent_id",
                "ref_table"=>array(
                    "table"=>"object_grouping_mem",
                    "this"=>"category_id",
                    "ref"=>"product_id"
                ),
            ),
        ),
        'reviews' => array(
            'title'=>'Reviews',
            'type'=>'object_multi',
            'subtype'=>'review',
            'readonly'=>false
        ),
        'image_id' => array(
            'title'=>'Image',
            'type'=>'object',
            'subtype'=>'file',
            'fkey_table'=>array("key"=>"id", "title"=>"file_title")
        ),
        'related_products' => array(
            'title'=>'Related Products',
            'type'=>'object_multi',
            'subtype'=>'product',
            'readonly'=>false
        ),
    ),
);
