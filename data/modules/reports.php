<?php

/**
 * Return navigation for entity of object type 'reports'
 */

namespace modules\navigation;

use Netric\Account\Module\LeftNavItemTypes;

// TODO: The Reports module is no longer installed by default

return [
    "title" => "Reports",
    "icon" => "AssignmentIcon",
    "default_route" => "all-reports",
    "name" => "reports",
    "short_title" => 'Reports',
    "scope" => 'system',
    "sort_order" => '11',
    "f_system" => true,
    "navigation" => [
        [
            "title" => "All Reports",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "route" => "all-reports",
            "objType" => "report",
            "icon" => "ViewListIcon",
            "browseby" => "groups",
        ]
    ]
];
