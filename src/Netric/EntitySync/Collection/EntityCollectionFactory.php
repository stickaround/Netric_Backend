<?php

declare(strict_types=1);

namespace Netric\EntitySync\Collection;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\EntitySync\Commit\CommitManagerFactory;
use Netric\EntitySync\DataMapperFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\WorkerMan\WorkerServiceFactory;

/**
 * Create a Entity Collection service
 */
class EntityCollectionFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Construct an instance of this factory so we can inject it as a dependency
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $commitManager = $serviceLocator->get(CommitManagerFactory::class);
        $index = $serviceLocator->get(IndexFactory::class);
        $workerService = $serviceLocator->get(WorkerServiceFactory::class);
        $collectionDataMapper = $serviceLocator->get(CollectionDataMapperFactory::class);

        return new EntityCollection($commitManager, $index, $workerService, $collectionDataMapper);
    }
}
