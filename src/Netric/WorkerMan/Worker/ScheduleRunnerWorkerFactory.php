<?php

declare(strict_types=1);

namespace Netric\WorkerMan\Worker;

use Aereus\ServiceContainer\ServiceContainerInterface;

/**
 * Construct worker called after each entity save
 */
class ScheduleRunnerWorkerFactory
{
    /**
     * Entity creation factory
     *
     * @param ServiceContainerInterface $serviceLocator For injecting dependencies
     * @return ScheduleRunnerWorker
     */
    public function create(ServiceContainerInterface $serviceLocator)
    {
        return new ScheduleRunnerWorker($serviceLocator->getApplication());
    }
}
