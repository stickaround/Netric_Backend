<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return array(
    'fields' => array(
        'name' => array(
            'title'=>'Name',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false
        ),
        'notes' => array(
            'title'=>'Notes',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>false
        ),
        'price' => array(
            'title'=>'Price',
            'type'=>Field::TYPE_NUMBER,
            'subtype'=>'double precision',
            'readonly'=>false
        ),
        'f_available' => array(
            'title'=>'Available',
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
            'readonly'=>false,
            'default'=>array("value"=>"t", "on"=>"null")
        ),
        'rating' => array(
            'title'=>'Rating',
            'type'=>Field::TYPE_NUMBER,
            'subtype'=>'integer',
            'readonly'=>false
        ),
        'family' => array(
            'title'=>'Product Family',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'product_family',
            'readonly'=>false
        ),
        'categories' => array(
            'title'=>'Categories',
            'type'=>Field::TYPE_GROUPING_MULTI,
            'subtype'=>'object_groupings',
            'fkey_table'=>array("key"=>"id", "title"=>"name", "parent"=>"parent_id",
                "ref_table"=>array(
                    "table"=>"object_grouping_mem",
                    "this"=>"object_id",
                    "ref"=>"grouping_id"
                ),
            ),
        ),
        'reviews' => array(
            'title'=>'Reviews',
            'type'=>Field::TYPE_OBJECT_MULTI,
            'subtype'=>'review',
            'readonly'=>false
        ),
        'image_id' => array(
            'title'=>'Image',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'file',
            'fkey_table'=>array("key"=>"id", "title"=>"file_title")
        ),
        'related_products' => array(
            'title'=>'Related Products',
            'type'=>Field::TYPE_OBJECT_MULTI,
            'subtype'=>'product',
            'readonly'=>false
        ),
    ),
);
