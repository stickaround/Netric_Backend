<?php

/**
 * Return navigation for entity of object type 'infocenter'
 *
 * browse-leftnav is a new navigation type where it will display the list of entities in the left navigation
 */

namespace modules\navigation;

return [
    "title" => "Knowledge",
    "icon" => "LightbulbOutlineIcon",
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
            "icon" => "AssignmentIcon",
        ],
        [
            "type" => "list-subheader",
            "title" => "Spaces",
            "route" => "spaces",
        ],
        [
            "type" => "browse-leftnav",
            "route" => "spaces",
            "browser_view" => "spaces",
            "objType" => "infocenter_document"
        ],
        [
            "title" => "All Documents",
            "type" => "browse",
            "route" => "spaces",
            "objType" => "infocenter_document",
            "icon" => "SearchIcon",
        ]
    ]
];
