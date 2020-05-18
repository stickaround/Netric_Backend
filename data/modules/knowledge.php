<?php

/**
 * Return navigation for entity of object type 'infocenter'
 */

namespace modules\navigation;

return [
    "title" => "Knowledge",
    "icon" => "LightBulbIcon",
    "default_route" => "all-notes",
    "name" => "knowledge",
    "short_title" => 'Knowledge',
    "scope" => 'system',
    "sort_order" => '10',
    "f_system" => true,
    "navigation" => [
        [
            "title" => "All Notes",
            "type" => "browse",
            "route" => "all-notes",
            "objType" => "note",
            "icon" => "ViewListIcon",
        ],
        [
            "title" => "All Spaces",
            "type" => "browse",
            "route" => "all-documents",
            "objType" => "infocenter_document",
            "icon" => "StyleIcon",
        ],
        [
            "title" => "Spaces",
            "type" => "browse",
            "browser_view" => "spaces",
            "route" => "spaces",
            "objType" => "infocenter_document",
            "icon" => "ChevronRightIcon",
        ]
    ]
];
