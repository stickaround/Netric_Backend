<?php

/**
 * Return navigation for entity of object type 'calendar'
 */

namespace modules\navigation;

use Netric\Account\Module\LeftNavItemTypes;

return [
    "title" => "Calendar",
    "icon" => "DateRangeIcon",
    "default_route" => "all-calendars",
    "name" => "calendar",
    "short_title" => 'Calendar',
    "scope" => 'system',
    "sort_order" => '5',
    "f_system" => true,
    "navigation" => [
        [
            "title" => "All Calendars",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "route" => "all-calendars",
            "objType" => "calendar",
            "icon" => "ViewListIcon",
            "browseby" => "groups",
        ]
    ]
];
