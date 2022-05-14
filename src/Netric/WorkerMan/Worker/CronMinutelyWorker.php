<?php

namespace Netric\WorkerMan\Worker;

use Netric\Account\AccountContainerInterface;
use Netric\Log\LogInterface;
use Netric\WorkerMan\Job;
use Netric\WorkerMan\AbstractWorker;
use Netric\WorkerMan\WorkerService;
use Netric\WorkerMan\WorkerServiceInterface;

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
    private WorkerServiceInterface $workerService;

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
        WorkerServiceInterface $workerService,
        LogInterface $log
    ) {
        $this->accountContainer = $accountContainer;
        $this->workerService = $workerService;
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
        // TODO: handle anything that needs to happen every minute
        return true;
    }
}
