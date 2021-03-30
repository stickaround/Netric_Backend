<?php

declare(strict_types=1);

namespace Netric\Account\InitData\Sets;

use Netric\Account\Account;
use Netric\Account\InitData\InitDataInterface;
use Netric\Entity\EntityLoader;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\EntityQuery\EntityQuery;
use Netric\WorkerMan\SchedulerService;
use Netric\EntityDefinition\ObjectTypes;

/**
 * Initializer to make sure accounts have a default set of groupings
 */
class WorkerJobsInitData implements InitDataInterface
{
    /**
     * List of users to create
     */
    private array $workerJobData = [];

    /**
     * Index used to query entities
     */
    private IndexInterface $entityIndex;

    /**
     * Scheudler used to scheudle recurring jobs
     */
    private SchedulerService $schedulerService;

    /**
     * Entity loader
     */
    private Entityloader $entityLoader;

    /**
     * Constructor
     *
     * @param array $workerJobData
     */
    public function __construct(
        array $workerJobData,
        IndexInterface $entityIndex,
        SchedulerService $schedulerService,
        EntityLoader $entityLoader
    ) {
        $this->workerJobData = $workerJobData;
        $this->entityIndex = $entityIndex;
        $this->schedulerService = $schedulerService;
        $this->entityLoader = $entityLoader;
    }

    /**
     * Insert or update initial data for account
     *
     * @param Account $account
     * @return bool
     */
    public function setInitialData(Account $account): bool
    {
        foreach ($this->workerJobData as $jobToSchedule) {
            /*
             * The way entity recurrence works in netric is that as long as at least
             * one entity exists with a recurrence pattern, then the entity query
             * can continue to create the scheduled jobs on query as needed. However,
             * if the worker_name was never added or all of them have been purged for
             * some reason then the recurring series will need to be restarted by adding
             * at least one instance.
             */
            $query = new EntityQuery(ObjectTypes::WORKER_JOB, $account->getAccountId());
            $query->where('worker_name')->equals($jobToSchedule['worker_name']);
            $result = $this->entityIndex->executeQuery($query);
            if (!$result->getTotalNum()) {
                // Create at least one instance
                $this->schedulerService->scheduleAtInterval(
                    $account->getSystemUser(),
                    $jobToSchedule['worker_name'],
                    $jobToSchedule['job_data'],
                    $jobToSchedule['recurrence']['type'],
                    $jobToSchedule['recurrence']['interval']
                );
            }
        }

        return true;
    }
}
