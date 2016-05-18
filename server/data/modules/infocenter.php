<?php
/**
 * Return navigation for entity of object type 'infocenter'
 */
namespace objects\navigation;

return array(
    "title" => "Infocenter",
    "icon" => "clipboard",
    "default_route" => "all-documents",
    "navigation" => array(
        array(
            "title" => "New Document",
            "type" => "entity",
            "route" => "new-document",
            "objType" => "infocenter_document",
            "icon" => "plus",
        ),
        array(
            "title" => "All Documents",
            "type" => "browse",
            "route" => "all-documents",
            "objType" => "infocenter_document",
            "icon" => "tags",
            "browseby" => "groups",
        )
    )
);