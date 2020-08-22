<?php

declare(strict_types=1);

namespace Netric\WorkerMan\Worker;

use Netric\Account\AccountContainerFactory;
use Netric\Account\Billing\AccountBillingServiceFactory;
use Netric\Log\LogFactory;
use Netric\WorkerMan\Job;
use Netric\WorkerMan\AbstractWorker;
use RuntimeException;

/**
 * The account billing worker will be responsible for billing each
 * account at a monthly interval.
 *
 * This job is added to the account automatically with data from
 * /data/account/worker-jobs.php
 */
class AccountBillingWorker extends AbstractWorker
{
    /**
     * Handle post-save actions
     *
     * @param Job $job
     * @return mixed The reversed string
     */
    public function work(Job $job)
    {
        $workload = $job->getWorkload();

        // Get the service manager - TODO: we will get away from this shortly
        $serviceManager = $this->getApplication()->getServiceManager();

        // Get the account
        $accountContainer = $serviceManager->get(AccountContainerFactory::class);
        $account = $accountContainer->loadById($workload['account_id']);
        $serviceManager = $account->getServiceManager();
        $log = $serviceManager->get(LogFactory::class);

        // Get number of active users for the account
        $accBillingSvc = $serviceManager->get(AccountBillingServiceFactory::class);

        $log->info(
            __CLASS__ .
                ': billing job ' .
                var_export($workload, true)
        );

        // Bill the account
        try {
            $accBillingSvc->billAmountDue($account);
        } catch (RuntimeException $exception) {
            $log->error(
                "Failed while trying to bill account(" .
                    $account->getAccountId() .
                    '):' .
                    $exception->getMessage()
            );
        }

        return true;
    }
}
