<?php

namespace modules\navigation;

/**
 * Return navigation for entity of work module
 *
 * The browser_view property in navigation refers to the views that is set in
 * objType's browser views (e.g. server/data/browser_views/task.php)
 */

return [
    "title" => "Work",
    "icon" => "WorkIcon",
    "default_route" => "my-task",
    "name" => "work",
    "short_title" => 'Work',
    "scope" => 'system',
    "sort_order" => '6',
    "f_system" => true,
    "navigation" => [
        [
            "title" => "My Tasks",
            "type" => "browse",
            "browser_view" => "my_task",
            "route" => "my-task",
            "objType" => "task",
            "icon" => "CheckIcon",
        ],
        [
            "title" => "Delegated Tasks",
            "type" => "browse",
            "browser_view" => "tasks_i_have_assigned",
            "route" => "delegated-task",
            "objType" => "task",
            "icon" => "AssignmentIndIcon",
        ],
        [
            "title" => "All Tasks",
            "type" => "browse",
            "browser_view" => "all_tasks",
            "route" => "all-task",
            "objType" => "task",
            "icon" => "DoneAllIcon",
        ],
        [
            "type" => "list-subheader",
            "title" => "Projects",
            "route" => "projects"
        ],
        [
            "type" => "browse-leftnav",
            "route" => "projects",
            "browser_view" => "my_open_projects",
            "objType" => "project",
            "icon" => "DoneAllIcon",
        ],
        [
            "title" => "All Projects",
            "type" => "browse",
            "route" => "projects",
            "objType" => "project",
            "icon" => "SearchIcon",
        ]
    ]
];
