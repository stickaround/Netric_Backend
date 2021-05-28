<?php

namespace modules\navigation;

use Netric\Account\Module\LeftNavItemTypes;

/**
 * Return navigation for entity of object type 'home'
 */

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
            "type" => LeftNavItemTypes::DASHBOARD,
            "route" => "activity",
            "objType" => "dashboard",
            "icon" => "DashboardIcon"
        ]
    ]
];
