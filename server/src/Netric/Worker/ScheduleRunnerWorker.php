<?php
namespace Netric\Worker;

use Netric\WorkerMan\Job;
use Netric\WorkerMan\AbstractWorker;
use Netric\WorkerMan\WorkerService;
use Netric\WorkerMan\SchedulerService;
use RuntimeException;

/**
 * This worker is used find and execute scheduled jobs for an account
*/
class ScheduleRunnerWorker extends AbstractWorker
{
    /**
     * Run all scheduled jobs as a background job and remove it from the scheduled queue
     *
     * @param Job $job
     * @return mixed The IDs of all run jobs
     */
    public function work(Job $job)
    {
        $workload = $job->getWorkload();

        // Make sure that the account ID was provided
        if (!$workload['account_id']) {
            throw new RuntimeException(
                "ScheduleRunnerWorker requires 'account_id' be set in the workload params"
            );
        }

        // Example of getting the current working application
        $application = $this->getApplication();
        $account = $application->getAccount($workload['account_id']);
        $workerService = $application->getServiceManager()->get(WorkerService::class);
        $schedulerService = $account->getServiceManager()->get(SchedulerService::class);

        $scheduledJobs = $schedulerService->getScheduledToRun();
        foreach ($scheduledJobs as $jobEntity) {
            $workerName = $jobEntity->getValue("worker_name");
            $jobData = json_decode($jobEntity->getValue("job_data"), true);
            $jobData['account_id'] = $workload['account_id'];

            // Queue the work to do by the next available worker
            $workerService->doWorkBackground($workerName, $jobData);

            // Flag the job as executed so we do not try to run it again
            $schedulerService->setJobAsExecuted($jobEntity);
        }

        return $this->result;
    }
}