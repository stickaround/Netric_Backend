<?php
namespace modules\navigation;

/**
 * Return navigation for entity of object type 'content'
 */
return [
    "title" => "Content",
    "icon" => "LocalLibraryIcon",
    "default_route" => "sites",
    "name" => "cms",
    "short_title" => 'Content',
    "scope" => 'system',
    "sort_order" => '9',
    "f_system" => true,
    "navigation" => [
        [
            "title" => "Sites",
            "type" => "browse",
            "route" => "sites",
            "objType" => "cms_site",
            "icon" => "ViewListIcon",
        ],
        [
            "title" => "Feeds",
            "type" => "browse",
            "route" => "feeds",
            "objType" => "content_feed",
            "icon" => "ViewListIcon",
        ]
    ]
];