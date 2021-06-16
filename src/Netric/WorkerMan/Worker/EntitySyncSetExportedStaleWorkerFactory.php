<?php

declare(strict_types=1);

namespace Netric\WorkerMan\Worker;

use Aereus\ServiceContainer\ServiceContainerInterface;

/**
 * Construct worker called to mark a commit as stale for all sync collections
 */
class EntitySyncSetExportedStaleWorkerFactory
{
    /**
     * Entity creation factory
     *
     * @param ServiceContainerInterface $serviceLocator For injecting dependencies
     * @return EntitySyncSetExportedStaleWorker
     */
    public function create(ServiceContainerInterface $serviceLocator)
    {
        return new EntitySyncSetExportedStaleWorker($serviceLocator->getApplication());
    }
}
