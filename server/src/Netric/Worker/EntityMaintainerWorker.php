<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2017 Aereus
 */
namespace Netric\Worker;

use Netric\WorkerMan\Job;
use Netric\WorkerMan\AbstractWorker;
use Netric\Entity\EntityMaintainerService;

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
        $workload = $job->getWorkload();
        $application = $this->getApplication();
        $log = $application->getLog();

        $log->info("EntityMaintainerWorker->work: [STARTED]");

        // Make sure we have the required data
        if (!isset($workload['account_id'])) {
            $log->error(
                "EntityMaintainerWorker->work: fields required account_id " .
                var_export($workload, true)
            );
            return false;
        }

        $log->info("EmailMailboxSyncWorker->work: for {$workload['account_id']}, {$workload['user_id']}");

        // Get the account and user we are working with
        $application = $this->getApplication();
        $account = $application->getAccount($workload['account_id']);

        $maintainerServices = $account->getServiceManager()->get(EntityMaintainerService::class);

        // TODO: Perform any cleanup here

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
