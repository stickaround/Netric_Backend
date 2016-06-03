<?php
/**
 * Return navigation for entity of object type 'email_message'
 */
namespace modules\navigation;

return array(
    "title" => "Email Message",
    "icon" => "envelope-o",
    "default_route" => "all-emails",
    "navigation" => array(
        array(
            "title" => "New Email",
            "type" => "entity",
            "route" => "new-emails",
            "objType" => "email_message",
            "icon" => "plus",
        ),
        array(
            "title" => "All Emails",
            "type" => "browse",
            "route" => "all-emails",
            "objType" => "email_message",
            "icon" => "tags",
            "browseby" => "groups",
        )
    )
);