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
            // TODO: Run the job in the background (inclduing json_decode the job_data)
            // TODO: Mark the job as executed
        }

        return $this->result;
    }
}