<?php

declare(strict_types=1);

namespace Netric\EntitySync\Collection;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\EntitySync\Commit\CommitManagerFactory;
use Netric\EntitySync\DataMapperFactory;
use Netric\EntitySync\EntitySync;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\WorkerMan\WorkerServiceFactory;
use Netric\EntityGroupings\DataMapper\EntityGroupingDataMapperFactory;

/**
 * Create a Grouping Collection service
 */
class GroupingCollectionFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Construct an instance of this factory so we can inject it as a dependency
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {        
        $commitManager = $serviceLocator->get(CommitManagerFactory::class);        
        $workerService = $serviceLocator->get(WorkerServiceFactory::class);
        $collectionDataMapper = $serviceLocator->get(CollectionDataMapperFactory::class);
        $groupingDataMapper = $serviceLocator->get(EntityGroupingDataMapperFactory::class);

        return new GroupingCollection($commitManager, $workerService, $collectionDataMapper, $groupingDataMapper);
    }
}
