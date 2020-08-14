<?php

declare(strict_types=1);

namespace Netric\WorkerMan\Worker;

use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Construct worker called after each entity save
 */
class ScheduleRunnerWorkerFactory
{
    /**
     * Entity creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator For injecting dependencies
     * @return ScheduleRunnerWorker
     */
    public function create(ServiceLocatorInterface $serviceLocator)
    {
        return new ScheduleRunnerWorker($serviceLocator->getApplication());
    }
}
