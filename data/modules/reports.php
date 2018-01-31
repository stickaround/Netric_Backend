<?php
/**
 * Return navigation for entity of object type 'reports'
 */
namespace modules\navigation;

return array(
    "title" => "Reports",
    "icon" => "AssignmentIcon",
    "default_route" => "all-reports",
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