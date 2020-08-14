<?php

/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2017 Aereus
 */

namespace Netric\WorkerMan\Worker;

use Netric\WorkerMan\Job;
use Netric\WorkerMan\AbstractWorker;
use Netric\Entity\EntityMaintainerService;
use Netric\Entity\EntityMaintainerServiceFactory;

/**
 * This worker is used to perform various cleanup operations on entity collections
 */
class EntityMaintainerWorker extends AbstractWorker
{
    /**
     * Cache the result
     *
     * @var string
     */
    private $result = "";

    /**
     * Start entity maintenance tasks
     *
     * @param Job $job
     * @return bool true on success, false on failure
     */
    public function work(Job $job)
    {
        // For now we are doing nothing
        return true;

        $application = $this->getApplication();
        $log = $application->getLog();
        $workerLockUniqueName = 'EntityMaintainerWorker';

        $log->info("EntityMaintainerWorker->work: [STARTED]");

        // Get the account and user we are working with
        $application = $this->getApplication();

        // Obtain a lock to make sure that only one instance of this worker runs on the system
        if (!$application->acquireLock($workerLockUniqueName, 86400)) {
            $log->info("EntityMaintainerWorker->work: could not obtain lock, must already be running or stuck");
            return true;
        }

        /*
         * Get all accounts for this application. If we are executing under a specific version
         * like 'beta' then Application::getAccounts will filter out only the accounts
         * set to use that version automatically.
         */
        $accounts = $application->getAccounts();
        foreach ($accounts as $account) {
            // Get the maintainer service for this account
            $maintainerService = $account->getServiceManager()->get(EntityMaintainerServiceFactory::class);

            // Log that we have started since processing may take a while
            $log->info("EntityMaintainerWorker->work: maintaining " . $account->getName());

            // Process all maintenance tasks
            $processed = $maintainerService->runAll($account->getAccountId());

            // Log what we did
            $log->info(
                "EntityMaintainerWorker->work: done " .
                    $account->getName() .
                    ": " .
                    var_export($processed, true)
            );
        }

        // Release the lock so we can run again/elsewhere
        $application->releaseLock($workerLockUniqueName);

        return true;
    }

    /**
     * Get the results of the last job
     *
     * @return string
     */
    public function getResult()
    {
        return $this->result;
    }
}
