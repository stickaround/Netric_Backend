<?php
/**
 * Return navigation for entity of object type 'content'
 */
namespace objects\navigation;

return array(
    "title" => "Content",
    "icon" => "newspaper-o",
    "default_route" => "all-contents",
    "navigation" => array(
        array(
            "title" => "New Content",
            "type" => "entity",
            "route" => "new-content",
            "objType" => "content_feed",
            "icon" => "plus",
        ),
        array(
            "title" => "All Contents",
            "type" => "browse",
            "route" => "all-contents",
            "objType" => "content_feed",
            "icon" => "list-ul",
            "browseby" => "groups",
        )
    )
);