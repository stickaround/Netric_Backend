<?php

namespace Netric\WorkerMan\Worker;

use Netric\Account\AccountContainerInterface;
use Netric\Account\Billing\AccountBillingServiceInterface;
use Netric\Log\LogInterface;
use Netric\WorkerMan\Job;
use Netric\WorkerMan\AbstractWorker;
use Netric\WorkerMan\WorkerService;

/**
 * This worker is used to test the WorkerMan
 */
class CronDailyWorker extends AbstractWorker
{
    /**
     * Container used to load acconts
     *
     * @var AccountContainerInterface
     */
    private AccountBillingServiceInterface $accountBillingService;

    /**
     * Account container used to load netric accounts
     *
     * @var AccountContainerInterface
     */
    private AccountContainerInterface $accountContainer;

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
        AccountBillingServiceInterface $accountBillingService,
        LogInterface $log
    ) {
        $this->accountContainer = $accountContainer;
        $this->accountBillingService = $accountBillingService;
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
        // First thing we do every day is bill due accounts
        $this->accountBillingService->billAllDueAccounts();

        return true;
    }
}
