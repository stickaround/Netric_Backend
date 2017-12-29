<?php
namespace data\entity_definitions;

return array(
    'revision' => 22,
    'fields' => array(
        'name' => array(
            'title'=>'Name',
            'type'=>'text',
            'subtype'=>'512',
            'readonly'=>false
        ),
        'description' => array(
            'title'=>'Description',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>false
        ),
        'dataware_cube' => array(
            'title'=>'DW Cube Path', 'type'=>'text', 'subtype'=>'512', 'readonly'=>false
        ),
        'custom_report' => array(
            'title'=>'Custom Report', 'type'=>'text', 'subtype'=>'512', 'readonly'=>true
        ),
        'obj_type' => array(
            'title'=>'Object Type', 'type'=>'text', 'subtype'=>'256', 'readonly'=>true
        ),
        'scope' => array(
            'title'=>'Scope',
            'type'=>'text',
            'subtype'=>'32',
            'optional_values'=>array("system"=>"System/Everyone", "user"=>"User")
        ),
        'groups' => array(
            'title'=>'Groups',
            'type'=>'fkey_multi',
            'subtype'=>'object_groupings',
            'fkey_table'=>array("key"=>"id", "title"=>"name", "parent"=>"parent_id",
                "ref_table"=>array(
                    "table"=>"object_grouping_mem",
                    "this"=>"object_id",
                    "ref"=>"grouping_id"
                )
            )
        ),
        'owner_id' => array(
            'title'=>'Owner',
            'type'=>'fkey',
            'subtype'=>'users',
            'default'=>array("value"=>"-3", "on"=>"null"),
            'fkey_table'=>array("key"=>"id", "title"=>"name")
        ),
        'f_display_table' => array(
            'title'=>'Display Table',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>false
        ),
        'f_display_chart' => array(
            'title'=>'Display Chart',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>false
        ),
        // Chart properties
        'chart_type' => array(
            'title'=>'Chart Type',
            'type'=>'text',
            'subtype'=>'128',
            'readonly'=>true
        ),
        'chart_measure' => array(
            'title'=>'X-Axis',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>true
        ),
        'chart_measure_agg' => array(
            'title'=>'X-Axis Aggregate',
            'type'=>'text',
            'subtype'=>'32',
            'readonly'=>true
        ),
        'chart_dim1' => array(
            'title'=>'Y-Axis',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>true
        ),
        'chart_dim1_grp' => array(
            'title'=>'Y-Axis By',
            'type'=>'text',
            'subtype'=>'32',
            'readonly'=>true
        ),
        'chart_dim2' => array(
            'title'=>'Grouping',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>true
        ),
        'chart_dim2_grp' => array(
            'title'=>'Grouping By',
            'type'=>'text',
            'subtype'=>'32',
            'readonly'=>true
        ),
        // Table properties
        'table_type' => array(
            'title'=>'Table Type',
            'type'=>'text',
            'subtype'=>'128',
            'readonly'=>false
        ),
        'f_row_totals' => array(
            'title'=>'Row Totals',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>false
        ),
        'f_column_totals' => array(
            'title'=>'Column Totals',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>false
        ),
        'f_sub_totals' => array(
            'title'=>'Subtotals',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>false
        ),
    ),
);
