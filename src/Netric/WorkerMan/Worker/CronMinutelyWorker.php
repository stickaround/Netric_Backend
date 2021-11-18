<?php

namespace Netric\WorkerMan\Worker;

use Netric\Account\AccountContainerInterface;
use Netric\Log\LogInterface;
use Netric\WorkerMan\Job;
use Netric\WorkerMan\AbstractWorker;
use Netric\WorkerMan\SchedulerService;
use Netric\WorkerMan\WorkerService;
use DateTime;

/**
 * This worker is used to test the WorkerMan
 */
class CronMinutelyWorker extends AbstractWorker
{
    /**
     * Container used to load acconts
     *
     * @var AccountContainerInterface
     */
    private AccountContainerInterface $accountContainer;

    /**
     * Service for interacting with workers
     *
     * @var WorkerService
     */
    private WorkerService $workerService;

    /**
     * Service for scheduling workers
     *
     * @var SchedulerService
     */
    private SchedulerService $schedulerService;

    /**
     * @var LogInterface
     */
    private LogInterface $log;

    /**
     * Inject depedencies
     *
     * @param AccountContainerInterface $accountContainer
     * @param WorkerService $workerService
     */
    public function __construct(
        AccountContainerInterface $accountContainer,
        WorkerService $workerService,
        SchedulerService $schedulerService,
        LogInterface $log
    ) {
        $this->accountContainer = $accountContainer;
        $this->workerService = $workerService;
        $this->schedulerService = $schedulerService;
        $this->log = $log;
    }

    /**
     * Process any jobs that should be run each minute
     *
     * @param Job $job
     * @return mixed The reversed string
     */
    public function work(Job $job)
    {

        $allActiveAccounts = $this->accountContainer->getAllActiveAccounts();
        foreach ($allActiveAccounts as $accountData) {
            $this->scheduleJobForAccount($accountData['account_id']);
        }

        return true;
    }

    /**
     * Get scheduled jobs for this account
     *
     * @param string $accountId
     * @return void
     */
    private function scheduleJobForAccount(string $accountId)
    {
        $account = $this->accountContainer->loadById($accountId);

        $toDate = new DateTime();
        $this->log->info(
            "ScheduleRunnerWorker->work: getting to time " .
                date('c', $toDate->getTimestamp())
        );

        $scheduledJobs = $this->schedulerService->getScheduledToRun($accountId);

        $this->log->info(
            "ScheduleRunnerWorker->work: Scheduling " . count($scheduledJobs) . " jobs to run"
        );

        foreach ($scheduledJobs as $jobEntity) {
            $workerName = $jobEntity->getValue("worker_name");
            $jobData = json_decode($jobEntity->getValue("job_data"), true);
            $jobData['account_id'] = $accountId;

            // Queue the work to do by the next available worker
            $this->workerService->doWorkBackground($workerName, $jobData);

            // Flag the job as executed so we do not try to run it again
            $this->schedulerService->setJobAsExecuted(
                $jobEntity,
                $account->getSystemUser()
            );

            $this->log->info(
                "ScheduleRunnerWorker->work: Executed $workerName for " . $jobData['account_id']
            );
        }
    }
}
