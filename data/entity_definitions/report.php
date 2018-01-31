<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return array(
    'fields' => array(
        'name' => array(
            'title'=>'Name',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'512',
            'readonly'=>false
        ),
        'description' => array(
            'title'=>'Description',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>false
        ),
        'dataware_cube' => array(
            'title'=>'DW Cube Path',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'512',
            'readonly'=>false
        ),
        'custom_report' => array(
            'title'=>'Custom Report',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'512',
            'readonly'=>true
        ),
        'obj_type' => array(
            'title'=>'Object Type',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>true
        ),
        'scope' => array(
            'title'=>'Scope',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'32',
            'optional_values'=>array("system"=>"System/Everyone", "user"=>"User")
        ),
        'groups' => array(
            'title'=>'Groups',
            'type'=>Field::TYPE_GROUPING_MULTI,
            'subtype'=>'object_groupings',
        ),
        'owner_id' => array(
            'title'=>'Owner',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'users',
            'default'=>array("value"=>"-3", "on"=>"null"),
        ),
        'f_display_table' => array(
            'title'=>'Display Table',
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
            'readonly'=>false
        ),
        'f_display_chart' => array(
            'title'=>'Display Chart',
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
            'readonly'=>false
        ),
        // Chart properties
        'chart_type' => array(
            'title'=>'Chart Type',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'128',
            'readonly'=>true
        ),
        'chart_measure' => array(
            'title'=>'X-Axis',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>true
        ),
        'chart_measure_agg' => array(
            'title'=>'X-Axis Aggregate',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'32',
            'readonly'=>true
        ),
        'chart_dim1' => array(
            'title'=>'Y-Axis',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>true
        ),
        'chart_dim1_grp' => array(
            'title'=>'Y-Axis By',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'32',
            'readonly'=>true
        ),
        'chart_dim2' => array(
            'title'=>'Grouping',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>true
        ),
        'chart_dim2_grp' => array(
            'title'=>'Grouping By',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'32',
            'readonly'=>true
        ),
        // Table properties
        'table_type' => array(
            'title'=>'Table Type',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'128',
            'readonly'=>false
        ),
        'f_row_totals' => array(
            'title'=>'Row Totals',
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
            'readonly'=>false
        ),
        'f_column_totals' => array(
            'title'=>'Column Totals',
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
            'readonly'=>false
        ),
        'f_sub_totals' => array(
            'title'=>'Subtotals',
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
            'readonly'=>false
        ),
    ),
);
