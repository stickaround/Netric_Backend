<?php

/**
 * Return navigation for entity of work module
 */

namespace modules\navigation;

return array(
    "title" => "Work",
    "icon" => "WorkIcon",
    "default_route" => "tasks",
    "name" => "work",
    "short_title" => 'Work',
    "scope" => 'system',
    "sort_order" => '6',
    "f_system" => true,
    "navigation" => array(
        array(
            "title" => "Tasks",
            "type" => "browse",
            "route" => "tasks",
            "objType" => "task",
            "icon" => "ViewListIcon"
        ),
        array(
            "title" => "My Task",
            "type" => "view",
            "route" => "my_task",
            "objType" => "task",
            "icon" => "ChevronRightIcon",
        ),
        array(
            "title" => "All Tasks",
            "type" => "view",
            "route" => "all_tasks",
            "objType" => "task",
            "icon" => "ChevronRightIcon",
        ),
        array(
            "title" => "Projects",
            "type" => "browse",
            "route" => "projects",
            "objType" => "project",
            "icon" => "ViewListIcon",
        )
    )
);
