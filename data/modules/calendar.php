<?php
/**
 * Return navigation for entity of object type 'calendar'
 */
namespace modules\navigation;

return array(
    "title" => "Calendar",
    "icon" => "DateRangeIcon",
    "default_route" => "all-calendars",
    "navigation" => array(
        array(
            "title" => "All Calendars",
            "type" => "browse",
            "route" => "all-calendars",
            "objType" => "calendar",
            "icon" => "ViewListIcon",
            "browseby" => "groups",
        )
    )
);