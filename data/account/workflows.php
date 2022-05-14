<?php

namespace data\account;

use Netric\Entity\ObjType\TaskEntity;
use Netric\Entity\ObjType\TicketEntity;
use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Workflow\WorkflowScheudleTimes;

return [
    [
        "name" => "Close Completed Tasks After 30 Minutes",
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
                "data" => [
                    // Launch if status=completed or status=deferred
                    'conditions' => [
                        [
                            'blogic' => Where::COMBINED_BY_AND,
                            'field_name' => 'is_closed',
                            'operator' => Where::OPERATOR_NOT_EQUAL_TO,
                            'value' => true
                        ],
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
                ],
                "child_actions" => [
                    [
                        "name" => "Wait 30 minutes",
                        "uname" => "task-close-on-complete-wait",
                        "type_name" => "wait_condition",
                        "f_system" => true,
                        "data" => [
                            'when_unit' => WorkflowScheudleTimes::TIME_UNIT_MINUTE,
                            'when_interval' => 30
                        ],
                        "child_actions" => [
                            [
                                "name" => "Close the Task",
                                "uname" => "task-close-on-complete-close",
                                "type_name" => "update_field",
                                "f_system" => true,
                                "data" => [
                                    'update_field' => "is_closed",
                                    'update_value' => true
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                "name" => "Check for Reopen Conditions",
                "uname" => "task-reopen-on-status-change-condition",
                "type_name" => "check_condition",
                "f_system" => true,
                "data" => [
                    // Launch if status!=completed and status!=deferred
                    'conditions' => [
                        [
                            'blogic' => Where::COMBINED_BY_AND,
                            'field_name' => 'is_closed',
                            'operator' => Where::OPERATOR_EQUAL_TO,
                            'value' => true
                        ],
                        [
                            'blogic' => Where::COMBINED_BY_AND,
                            'field_name' => 'status_id',
                            'operator' => Where::OPERATOR_NOT_EQUAL_TO,
                            'value' => TicketEntity::STATUS_SOLVED,
                        ],
                        [
                            'blogic' => Where::COMBINED_BY_OR,
                            'field_name' => 'status_id',
                            'operator' => Where::OPERATOR_NOT_EQUAL_TO,
                            'value' => TicketEntity::STATUS_UNSOLVABLE,
                        ]
                    ]
                ],
                "child_actions" => [
                    [
                        "name" => "Close the Ticket",
                        "uname" => "task-reopen-on-incomplete",
                        "type_name" => "update_field",
                        "f_system" => true,
                        "data" => [
                            'update_field' => "is_closed",
                            'update_value' => false,
                        ],
                    ],
                ],
            ],
        ],
    ],
    [
        "name" => "Close Completed Ticket",
        "uname" => "ticket-close-on-complete",
        "notes" => "Closes a ticket if it gets marked as completed.",
        "object_type" => ObjectTypes::TICKET,
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
                "uname" => "ticket-close-on-complete-condition",
                "type_name" => "check_condition",
                "f_system" => true,
                "data" => [
                    // Launch if status=solved or status=unsolvable
                    'conditions' => [
                        [
                            'blogic' => Where::COMBINED_BY_AND,
                            'field_name' => 'is_closed',
                            'operator' => Where::OPERATOR_NOT_EQUAL_TO,
                            'value' => true
                        ],
                        [
                            'blogic' => Where::COMBINED_BY_AND,
                            'field_name' => 'status_id',
                            'operator' => Where::OPERATOR_EQUAL_TO,
                            'value' => TicketEntity::STATUS_SOLVED,
                        ],
                        [
                            'blogic' => Where::COMBINED_BY_OR,
                            'field_name' => 'status_id',
                            'operator' => Where::OPERATOR_EQUAL_TO,
                            'value' => TicketEntity::STATUS_UNSOLVABLE,
                        ]
                    ]
                ],
                "child_actions" => [
                    [
                        "name" => "Close the Ticket",
                        "uname" => "ticket-close-on-complete-close",
                        "type_name" => "update_field",
                        "f_system" => true,
                        "data" => [
                            'update_field' => "is_closed",
                            'update_value' => true
                        ],
                    ],
                ],
            ],
            [
                "name" => "Check for Reopen Conditions",
                "uname" => "ticket-reopen-on-incomplete-condition",
                "type_name" => "check_condition",
                "f_system" => true,
                "data" => [
                    // Launch if status!=solved or status!=unsolvable
                    'conditions' => [
                        [
                            'blogic' => Where::COMBINED_BY_AND,
                            'field_name' => 'is_closed',
                            'operator' => Where::OPERATOR_EQUAL_TO,
                            'value' => true
                        ],
                        [
                            'blogic' => Where::COMBINED_BY_AND,
                            'field_name' => 'status_id',
                            'operator' => Where::OPERATOR_NOT_EQUAL_TO,
                            'value' => TicketEntity::STATUS_SOLVED,
                        ],
                        [
                            'blogic' => Where::COMBINED_BY_OR,
                            'field_name' => 'status_id',
                            'operator' => Where::OPERATOR_NOT_EQUAL_TO,
                            'value' => TicketEntity::STATUS_UNSOLVABLE,
                        ]
                    ]
                ],
                "child_actions" => [
                    [
                        "name" => "Oepn the Ticket",
                        "uname" => "ticket-reoppen-on-incomplete-save",
                        "type_name" => "update_field",
                        "f_system" => true,
                        "data" => [
                            'update_field' => "is_closed",
                            'update_value' => false
                        ],
                    ],
                ],
            ],
        ],
    ],
];
