<?php

declare(strict_types=1);

namespace Netric\WorkerMan\Worker;

use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Construct worker called after each entity save
 */
class EntityDefinitionPostSaveWorkerFactory
{
    /**
     * Entity creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator For injecting dependencies
     * @return EntityDefinitionPostSaveWorker
     */
    public function create(ServiceLocatorInterface $serviceLocator)
    {
        return new EntityDefinitionPostSaveWorker($serviceLocator->getApplication());
    }
}
