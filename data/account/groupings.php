<?php

use Netric\Entity\ObjType\TaskEntity;
use Netric\Entity\ObjType\TicketEntity;
use Netric\Entity\ObjType\UserEntity;

/**
 * Default groupings for entities
 *
 * These values will be added if and only if there are no (0] groupings for the
 * field. This allows an administrator to completely change their groupings without
 * it being overwritten with each update.
 */
return [
    'user' => [
        'groups' => [
            ['name' => UserEntity::GROUP_USERS, 'f_admin' => 'f'],
            ['name' => UserEntity::GROUP_EVERYONE, 'f_admin' => 'f'],
            ['name' => UserEntity::GROUP_CREATOROWNER, 'f_admin' => 'f'],
            ['name' => UserEntity::GROUP_ADMINISTRATORS, 'f_admin' => 't'],
        ],
    ],
    'task' => [
        'status_id' => [
            ["name" => TaskEntity::STATUS_TODO, "sort_oder" => 1],
            ["name" => TaskEntity::STATUS_IN_PROGRESS, "sort_oder" => 2],
            // ["name" => TaskEntity::STATUS_IN_TEST, "sort_oder" => 3],
            // ["name" => TaskEntity::STATUS_IN_REVIEW, "sort_oder" => 4],
            ["name" => TaskEntity::STATUS_DEFERRED, "sort_oder" => 5],
            ["name" => TaskEntity::STATUS_COMPLETED, "sort_oder" => 6],
        ],
        'priority_id' => [
            ["name" => TaskEntity::PRIORITY_LOW, "sort_oder" => 1],
            ["name" => TaskEntity::PRIORITY_MEDIUM, "sort_oder" => 2],
            ["name" => TaskEntity::PRIORITY_HIGH, "sort_oder" => 3],
        ],
        'type_id' => [
            ["name" => TaskEntity::TYPE_ENHANCEMENT],
            ["name" => TaskEntity::TYPE_DEFECT],
            ["name" => TaskEntity::TYPE_SUPPORT],
        ]
    ],
    'ticket' => [
        'status_id' => [
            ["name" => TicketEntity::STATUS_NEW, "sort_oder" => 1],
            ["name" => TicketEntity::STATUS_IN_PROGRESS, "sort_oder" => 2],
            ["name" => TicketEntity::STATUS_WAITING, "sort_oder" => 3],
            ["name" => TicketEntity::STATUS_ON_HOLD, "sort_oder" => 4],
            ["name" => TicketEntity::STATUS_SOLVED, "sort_oder" => 5],
            ["name" => TicketEntity::STATUS_UNSOLVABLE, "sort_oder" => 6],
        ],
        'priority_id' => [
            ["name" => TicketEntity::PRIORITY_LOW, "sort_oder" => 1],
            ["name" => TicketEntity::PRIORITY_MEDIUM, "sort_oder" => 2],
            ["name" => TicketEntity::PRIORITY_HIGH, "sort_oder" => 3],
        ],
        'source_id' => [
            ["name" => TicketEntity::SOURCE_EMAIL],
        ]
    ],
    'marketing_campaign' => [
        'type_id' => [
            ["name" => "Email"],
            ["name" => "Advertisement"],
            ["name" => "Telephone"],
            ["name" => "Banner Ads"],
            ["name" => "Public Relations"],
            ["name" => "Partners"],
            ["name" => "Resellers"],
            ["name" => "Referral Program"],
            ["name" => "Direct Mail"],
            ["name" => "Trade Show"],
            ["name" => "Conference"],
            ["name" => "Other"],

        ],
        'status_id' => [
            ["name" => "Planning", "sort_oder" => 1],
            ["name" => "Active", "sort_oder" => 2],
            ["name" => "Inactive", "sort_oder" => 3],
            ["name" => "Complete", "sort_oder" => 4],
        ],
    ],
    'content_feed_post' => [
        'status_id' => [
            ["name" => "Draft"],
            ["name" => "Awaiting Review"],
            ["name" => "Rejected"],
            ["name" => "Published"],
        ],
    ],
    'cms_page' => [
        'status_id' => [
            ["name" => "Draft"],
            ["name" => "Awaiting Review"],
            ["name" => "Rejected"],
            ["name" => "Published"],
        ],
    ],
    'phone_call' => [
        'purpose_id' => [
            ["name" => "Prospecting"],
            ["name" => "Administrative"],
            ["name" => "Negotiation"],
            ["name" => "Demo"],
            ["name" => "Project"],
            ["name" => "Support"],
        ],
    ],
    'lead' => [
        'status_id' => [
            ["name" => "New: Not Contacted"],
            ["name" => "New: Pre Qualified"],
            ["name" => "Working: Attempted to Contact"],
            ["name" => "Working: Contacted"],
            ["name" => "Working: Contact Later"],
            ["name" => "Closed: Converted"],
            ["name" => "Closed: Lost"],
            ["name" => "Closed: Junk"],
        ],
        'rating_id' => [
            ["name" => "Hot"],
            ["name" => "Medium"],
            ["name" => "Cold"],
        ],
        'source_id' => [
            ["name" => "Advertisement"],
            ["name" => "Cold Call"],
            ["name" => "Employee Referral"],
            ["name" => "External Referral"],
            ["name" => "Website"],
            ["name" => "Partner"],
            ["name" => "Email"],
            ["name" => "Web Research"],
            ["name" => "Direct Mail"],
            ["name" => "Trade Show"],
            ["name" => "Conference"],
            ["name" => "Other"],
        ],
    ],
    'opportunity' => [
        'stage_id' => [
            ["name" => "Qualification"],
            ["name" => "Needs Analysis"],
            ["name" => "Value Proposition"],
            ["name" => "Id. Decision Makers"],
            ["name" => "Proposal/Price Quote"],
            ["name" => "Negotiation/Review"],
            ["name" => "Closed: Won"],
            ["name" => "Closed: Lost"],
        ],
        'type_id' => [
            ["name" => "New Business"],
            ["name" => "Existing Business"],
        ],
        'objection_id' => [
            ["name" => "Not Interested / Don't need it"],
            ["name" => "Already Working with Someone"],
            ["name" => "Trouble Getting Approved"],
            ["name" => "Price Too High"],
            ["name" => "Troubling Reputation"],
            ["name" => "Never Heard of Us"],
            ["name" => "Had Problems in the Past"],
            ["name" => "Too Confusing/Complex"],
            ["name" => "Not a Good Fit"],
        ],
        'selling_point_id' => [
            ["name" => "Price"],

            ["name" => "Features"],
            ["name" => "Good Reputation"],
            ["name" => "Support"],
            ["name" => "Simplicity"],
            ["name" => "Good Experience"],
        ],
    ],
];
