<?php
/**
 * Return navigation for entity of object type 'infocenter'
 */
namespace modules\navigation;

return array(
    "title" => "Knowledge",
    "icon" => "LightBulbIcon",
    "default_route" => "all-documents",
    "name" => "knowledge",
    "short_title" => 'Knowledge',
    "scope" => 'system',
    "sort_order" => '10',
    "f_system" => true,
    "navigation" => array(
        array(
            "title" => "All Documents",
            "type" => "browse",
            "route" => "all-documents",
            "objType" => "infocenter_document",
            "icon" => "StyleIcon",
            "browseby" => "groups",
        )
    )
);
