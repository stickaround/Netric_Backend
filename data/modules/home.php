<?php
/**
 * Return navigation for entity of object type 'home'
 */
namespace modules\navigation;

return array(
    "title" => "Home",
    "icon" => "tachometer",
    "default_route" => "activity",
    "navigation" => array(
        array(
            "title" => "Dashboards",
            "type" => "dashboard",
            "route" => "activity",
            "objType" => "dashboard",
            "icon" => "DashboardIcon"
        )
    )
);