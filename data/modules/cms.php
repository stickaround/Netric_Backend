<?php
namespace modules\navigation;

/**
 * Return navigation for entity of object type 'content'
 */
return [
    "title" => "Content",
    "icon" => "newspaper-o",
    "default_route" => "sites",
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