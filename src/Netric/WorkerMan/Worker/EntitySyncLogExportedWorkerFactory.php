<?php

declare(strict_types=1);

namespace Netric\WorkerMan\Worker;

use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Construct worker called after each entity save
 */
class EntitySyncLogExportedWorkerFactory
{
    /**
     * Entity creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator For injecting dependencies
     * @return EntitySyncLogExportedWorker
     */
    public function create(ServiceLocatorInterface $serviceLocator)
    {
        return new EntitySyncLogExportedWorker($serviceLocator->getApplication());
    }
}
