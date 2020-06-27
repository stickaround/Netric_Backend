<?php

/**
 * Return navigation for entity of object type 'infocenter'
 * 
 * browse-leftnav is a new navigation type where it will display the list of entities in the left navigation
 */

namespace modules\navigation;

return [
    "title" => "Knowledge",
    "icon" => "LightBulbIcon",
    "default_route" => "my-notes",
    "name" => "knowledge",
    "short_title" => 'Knowledge',
    "scope" => 'system',
    "sort_order" => '10',
    "f_system" => true,
    "navigation" => [
        [
            "title" => "My Notes",
            "type" => "browse",
            "route" => "my-notes",
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
        ],
        [
            "type" => "browse-leftnav",
            "route" => "spaces",
            "browser_view" => "spaces",
            "objType" => "infocenter_document"
        ]
    ]
];
