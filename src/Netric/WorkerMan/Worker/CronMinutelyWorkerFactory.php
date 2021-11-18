<?php

declare(strict_types=1);

namespace Netric\WorkerMan\Worker;

use Netric\Account\AccountContainerFactory;
use Netric\Log\LogFactory;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\WorkerMan\SchedulerServiceFactory;
use Netric\WorkerMan\WorkerServiceFactory;

/**
 * Construct worker called each minute like a cron job
 */
class CronMinutelyWorkerFactory
{
    /**
     * Entity creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator For injecting dependencies
     * @return TestWorker
     */
    public function create(ServiceLocatorInterface $serviceLocator)
    {
        $accountContainer = $serviceLocator->get(AccountContainerFactory::class);
        $workerService = $serviceLocator->get(WorkerServiceFactory::class);
        $schedulerService = $serviceLocator->get(SchedulerServiceFactory::class);
        $log = $serviceLocator->get(LogFactory::class);

        return new CronMinutelyWorker($accountContainer, $workerService, $schedulerService, $log);
    }
}
