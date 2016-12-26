<?php
/**
 * Return navigation for entity of messages
 */
namespace modules\navigation;

return array(
    "title" => "Messages",
    "icon" => "envelope-o",
    "default_route" => "all-messages",
    "navigation" => array(
        array(
            "title" => "All Messages",
            "type" => "browse",
            "route" => "all-messages",
            "objType" => "email_account",
            "icon" => "tags",
            //"browseby" => "groups",
        )
    )
);