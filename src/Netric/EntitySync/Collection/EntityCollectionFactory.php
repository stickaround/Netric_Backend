<?php

declare(strict_types=1);

namespace Netric\EntitySync\Collection;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\EntitySync\Commit\CommitManagerFactory;
use Netric\EntitySync\DataMapperFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\WorkerMan\WorkerServiceFactory;

/**
 * Create a Entity Collection service
 */
class EntityCollectionFactory implements FactoryInterface
{
    /**
     * Construct an instance of this factory so we can inject it as a dependency
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $commitManager = $serviceLocator->get(CommitManagerFactory::class);
        $index = $serviceLocator->get(IndexFactory::class);
        $workerService = $serviceLocator->get(WorkerServiceFactory::class);
        $collectionDataMapper = $serviceLocator->get(CollectionDataMapperFactory::class);

        return new EntityCollection($commitManager, $index, $workerService, $collectionDataMapper);
    }
}
