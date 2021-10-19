<?php

declare(strict_types=1);

namespace Netric\WorkerMan\Worker;

use Netric\ServiceManager\ServiceLocatorInterface;


/**
 * Construct worker called after each entity save
 */
class EntityPostSaveWorkerFactory
{
    /**
     * Entity creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator For injecting dependencies
     * @return EntityPostSaveWorker
     */
    public function create(ServiceLocatorInterface $serviceLocator)
    {
        return new EntityPostSaveWorker($serviceLocator->getApplication());
    }
}
