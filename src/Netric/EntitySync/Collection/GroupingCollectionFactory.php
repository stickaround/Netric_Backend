<?php

declare(strict_types=1);

namespace Netric\EntitySync\Collection;

use Netric\EntitySync\Commit\CommitManagerFactory;
use Netric\EntitySync\DataMapperFactory;
use Netric\EntitySync\EntitySync;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\ServiceManager\AccountServiceFactoryInterface;
use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\WorkerMan\WorkerServiceFactory;
use Netric\EntityGroupings\DataMapper\EntityGroupingDataMapperFactory;

/**
 * Create a Grouping Collection service
 */
class GroupingCollectionFactory implements AccountServiceFactoryInterface
{
    /**
     * Construct an instance of this factory so we can inject it as a dependency
     *
     * @param AccountServiceManagerInterface $serviceLocator ServiceLocator for injecting dependencies
     */
    public function createService(AccountServiceManagerInterface $serviceLocator)
    {        
        $commitManager = $serviceLocator->get(CommitManagerFactory::class);        
        $workerService = $serviceLocator->get(WorkerServiceFactory::class);
        $collectionDataMapper = $serviceLocator->get(CollectionDataMapperFactory::class);
        $groupingDataMapper = $serviceLocator->get(EntityGroupingDataMapperFactory::class);

        return new GroupingCollection($commitManager, $workerService, $collectionDataMapper, $groupingDataMapper);
    }
}
