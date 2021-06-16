<?php

declare(strict_types=1);

namespace Netric\WorkerMan\Worker;

use Aereus\ServiceContainer\ServiceContainerInterface;

/**
 * Construct worker called after each entity save
 */
class EntitySyncLogExportedWorkerFactory
{
    /**
     * Entity creation factory
     *
     * @param ServiceContainerInterface $serviceLocator For injecting dependencies
     * @return EntitySyncLogExportedWorker
     */
    public function create(ServiceContainerInterface $serviceLocator)
    {
        return new EntitySyncLogExportedWorker($serviceLocator->getApplication());
    }
}
