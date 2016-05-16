<?php
/**
 * Return navigation for entity of object type 'project'
 */
namespace objects\navigation;

return array(
    "title" => "Work",
    "icon" => "check-square-o",
    "default_route" => "all-tasks",
    "navigation" => array(
        array(
            "title" => "New Task",
            "type" => "entity",
            "route" => "new-task",
            "objType" => "task",
            "icon" => "plus",
        ),
        array(
            "title" => "All Tasks",
            "type" => "browse",
            "route" => "all-tasks",
            "objType" => "task",
            "icon" => "list-ul",
            "browseby" => "groups",
        ),
        array(
            "title" => "New Project",
            "type" => "entity",
            "route" => "new-project",
            "objType" => "project",
            "icon" => "plus",
        ),
        array(
            "title" => "All Projects",
            "type" => "browse",
            "route" => "all-projects",
            "objType" => "project",
            "icon" => "list-ul",
            "browseby" => "groups",
        ),
    ),
);