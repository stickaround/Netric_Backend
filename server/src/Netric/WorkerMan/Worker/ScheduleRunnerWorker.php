<?php
namespace Netric\WorkerMan\Worker;

use Netric\Application\Application;
use Netric\WorkerMan\Job;
use Netric\WorkerMan\AbstractWorker;
use Netric\WorkerMan\WorkerService;
use Netric\WorkerMan\SchedulerService;
use InvalidArgumentException;

/**
 * This worker is used find and execute scheduled jobs for an account
*/
class ScheduleRunnerWorker extends AbstractWorker
{
    /**
     * Scheduler service for working with scheduled work
     *
     * @var SchedulerService
     */
    private $schedulerService = null;

    /**
     * Service for sending jobs to be processed in the background
     *
     * @var WOrkerService
     */
    private $workerService = null;
    
    /**
     * Extend constructor in order to provide dependency injection for tests
     *
     * If a worker extends the constructor, it MUST call:
     * parent::__construct in order to setup the worker property.
     *
     * @param Application $application
     * @param SchedulerService $schedulerService For getting and updating scheduled work
     * @param WorkerSerivce $workerService For sending jobs to be run in the background
     */
     public function __construct(
         Application $application, 
         SchedulerService $schedulerService = null, 
         WorkerService $workerService = null)
     {
        parent::__construct($application);

        // SEt the worker service or load it with a service location if not set
        if ($workerService) {
            $this->workerService = $workerService;
        } else {
            $this->workerService = $application->getServiceManager()->get(WorkerService::class);
        }

         /*
          * We cannot load the scheduler service from the service locator because it is
          * account specific and we will not know the account we are processing for until
          * we get the job workload (see work() below for more details)
          */
        if ($schedulerService) {
            $this->schedulerService = $schedulerService;
        }
     }

    /**
     * Run all scheduled jobs as a background job and remove it from the scheduled queue
     *
     * @param Job $job
     * @return mixed The IDs of all run jobs
     */
    public function work(Job $job)
    {
        $idsOfRunJobs = [];

        $workload = $job->getWorkload();

        // Make sure that the account ID was provided
        if (!$workload['account_id']) {
            throw new InvalidArgumentException(
                "ScheduleRunnerWorker requires 'account_id' be set in the workload params"
            );
        }

        // Example of getting the current working application
        $application = $this->getApplication();
        $account = $application->getAccount($workload['account_id']);

        // If the scheduler service was not injected as a dependency then load it
        if (!$this->schedulerService) {
            $this->schedulerService = $account->getServiceManager()->get(SchedulerService::class);            
        }

        $scheduledJobs = $this->schedulerService->getScheduledToRun();
        $application->getLog()->info(
            "ScheduleRunnerWorker->work: Scheduling " . count($scheduledJobs) . " jobs to run"
        );

        foreach ($scheduledJobs as $jobEntity) {
            $workerName = $jobEntity->getValue("worker_name");
            $jobData = json_decode($jobEntity->getValue("job_data"), true);
            $jobData['account_id'] = $workload['account_id'];

            // Queue the work to do by the next available worker
            $idsOfRunJobs[] = $this->workerService->doWorkBackground($workerName, $jobData);

            // Flag the job as executed so we do not try to run it again
            $this->schedulerService->setJobAsExecuted($jobEntity);

            $application->getLog()->info(
                "ScheduleRunnerWorker->work: Executed $workerName for " . $jobData['account_id']
            );
        }

        return $idsOfRunJobs;
    }
}