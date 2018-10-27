<?php
/**
 * Return navigation for entity of object type 'reports'
 */
namespace modules\navigation;

// TODO: The Reports module is no longer installed by default

return array(
    "title" => "Reports",
    "icon" => "AssignmentIcon",
    "default_route" => "all-reports",
    "name" => "reports",
    "short_title" => 'Reports',
    "scope" => 'system',
    "sort_order" => '11',
    "f_system" => true,
    "navigation" => array(
        array(
            "title" => "All Reports",
            "type" => "browse",
            "route" => "all-reports",
            "objType" => "report",
            "icon" => "ViewListIcon",
            "browseby" => "groups",
        )
    )
);