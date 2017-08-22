<?php
/**
 * Add scheduled jobs
 */
use Netric\EntityQuery;
use Netric\WorkerMan\SchedulerService;

$account = $this->getAccount();
if (!$account)
    throw new \RuntimeException("This must be run only against a single account");

// Get data for creating WorkFlows
$workerJobsData = require(__DIR__ . "/../../../../data/account/worker-jobs.php");

// Get services
$serviceLocator = $account->getServiceManager();
$entityIndex = $serviceLocator->get('Netric/EntityQuery/Index/Index');
$schedulerService = $serviceLocator->get(SchedulerService::class);
$entityLoader = $serviceLocator->get('EntityLoader');

foreach ($workerJobsData as $jobToSchedule) {
    /*
     * The way entity recurrence works in netric is that as long as at least
     * one entity exists with a recurrence pattern, then the entity query
     * can continue to create the scheduled jobs on query as needed. However,
     * if the worker_name was never added or all of them have been purged for
     * some reason then the recurring series will need to be restarted by adding
     * at least one instance.
     */
    $query = new EntityQuery('worker_job');
    $query->where('worker_name', $jobToSchedule['worker_name']);
    $result = $entityIndex->executeQuery($query);
    if (!$result->getTotalNum()) {
        // Create at least one instance
        $schedulerService->scheduleAtInterval(
            $jobToSchedule['worker_name'], 
            $jobToSchedule['job_data'], 
            $jobToSchedule['recurrence']['type'], 
            $jobToSchedule['recurrence']['interval']
        );
    }
}