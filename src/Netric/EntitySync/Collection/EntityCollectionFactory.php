<?php

declare(strict_types=1);

namespace Netric\EntitySync\Collection;

use Netric\EntitySync\Commit\CommitManagerFactory;
use Netric\EntitySync\DataMapperFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\WorkerMan\WorkerServiceFactory;
use Netric\ServiceManager\AccountServiceFactoryInterface;
use Netric\ServiceManager\AccountServiceManagerInterface;

/**
 * Create a Entity Collection service
 */
class EntityCollectionFactory implements AccountServiceFactoryInterface
{
    /**
     * Construct an instance of this factory so we can inject it as a dependency
     *
     * @param AccountServiceManagerInterface $serviceLocator ServiceLocator for injecting dependencies
     */
    public function createService(AccountServiceManagerInterface $serviceLocator)
    {        
        $commitManager = $serviceLocator->get(CommitManagerFactory::class);
        $index = $serviceLocator->get(IndexFactory::class);
        $workerService = $serviceLocator->get(WorkerServiceFactory::class);
        $collectionDataMapper = $serviceLocator->get(CollectionDataMapperFactory::class);

        return new EntityCollection($commitManager, $index, $workerService, $collectionDataMapper);
    }
}