<?php

declare(strict_types=1);

namespace Netric\WorkerMan\Worker;

use Aereus\ServiceContainer\ServiceContainerInterface;

/**
 * Construct worker called after each entity save
 */
class EntityPostSaveWorkerFactory
{
    /**
     * Entity creation factory
     *
     * @param ServiceContainerInterface $serviceLocator For injecting dependencies
     * @return EntityPostSaveWorker
     */
    public function create(ServiceContainerInterface $serviceLocator)
    {
        return new EntityPostSaveWorker($serviceLocator->getApplication());
    }
}
