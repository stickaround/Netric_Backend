<?php

declare(strict_types=1);

namespace Netric\WorkerMan\Worker;

use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Construct worker called to mark a commit as stale for all sync collections
 */
class EntitySyncSetExportedStaleWorkerFactory
{
    /**
     * Entity creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator For injecting dependencies
     * @return EntitySyncSetExportedStaleWorker
     */
    public function create(ServiceLocatorInterface $serviceLocator)
    {
        return new EntitySyncSetExportedStaleWorker($serviceLocator->getApplication());
    }
}
