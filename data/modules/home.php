<?php

/**
 * Return navigation for entity of object type 'home'
 */

namespace modules\navigation;

return [
    "title" => "Home",
    "icon" => "HomeIcon",
    "default_route" => "activity",
    "name" => "home",
    "short_title" => 'Home',
    "scope" => 'system',
    "sort_order" => '1',
    "settings" => "none",
    "f_system" => true,
    "navigation" => [
        [
            "title" => "Dashboards",
            "type" => "dashboard",
            "route" => "activity",
            "objType" => "dashboard",
            "icon" => "DashboardIcon"
        ]
    ]
];
