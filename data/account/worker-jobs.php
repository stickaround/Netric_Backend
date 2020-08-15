<?php

namespace data\account;

use Netric\Entity\Recurrence\RecurrencePattern;
use Netric\WorkerMan\Worker\EntityMaintainerWorker;

/*
 * Set any recurring work that each account needs to execute here
 * and each time code is deployed we will make sure every account has
 * these jobs added.
 *
 * @see /bin/scripts/update/always/06-worker-jobs.php to see how these
 * are applied to each account.
 */

return [
    [
        'worker_name' => EntityMaintainerWorker::class,
        'job_data' => [],
        'recurrence' => [
            'type' => RecurrencePattern::RECUR_DAILY,
            'interval' => 1
        ]
    ],
];

// [
//     'worker_name' => 'AccountBilling',
//     'job_data' => [],
//     'recurrence' => [
//         'type' => RecurrencePattern::RECUR_DAILY,
//         'interval' => 1
//     ]
// ]