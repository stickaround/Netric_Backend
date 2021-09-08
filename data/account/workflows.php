<?php

namespace data\account;

use Netric\Entity\ObjType\TaskEntity;
use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Workflow\WorkflowScheudleTimes;

return [
    [
        "name" => "Close Completed Task After a Day",
        "uname" => "task-close-on-complete",
        "notes" => "Closes a task 24 hours after the status is set to completed or deferred",
        "object_type" => ObjectTypes::TASK,
        "f_active" => true,
        "f_on_create" => true,
        "f_on_update" => true,
        "f_on_delete" => false,
        "f_on_daily" => false,
        "f_singleton" => false,
        "f_system" => true,
        "child_actions" => [
            [
                "name" => "Check for Closed Conditions",
                "uname" => "task-close-on-complete-condition",
                "type_name" => "check_condition",
                "f_system" => true,
                "data" => json_encode([
                    // Launch if status=completed or status=deferred
                    'conditions' => [
                        [
                            'blogic' => Where::COMBINED_BY_AND,
                            'field_name' => 'status_id',
                            'operator' => Where::OPERATOR_EQUAL_TO,
                            'value' => TaskEntity::STATUS_COMPLETED
                        ],
                        [
                            'blogic' => Where::COMBINED_BY_OR,
                            'field_name' => 'status_id',
                            'operator' => Where::OPERATOR_EQUAL_TO,
                            'value' => TaskEntity::STATUS_DEFERRED
                        ]
                    ]
                ]),
                "child_actions" => [
                    [
                        "name" => "Wait 24 Hours",
                        "uname" => "task-close-on-complete-wait",
                        "type_name" => "wait_condition",
                        "f_system" => true,
                        "data" => json_encode([
                            'when_unit' => WorkflowScheudleTimes::TIME_UNIT_DAY,
                            'when_interval' => 1
                        ]),
                        "child_actions" => [
                            [
                                "name" => "Wait 24 Hours",
                                "uname" => "task-close-on-complete-close",
                                "type_name" => "update_field",
                                "f_system" => true,
                                "data" => json_encode([
                                    'update_field' => "is_closed",
                                    'update_value' => true
                                ]),
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ]
];
