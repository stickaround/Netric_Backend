<?php

declare(strict_types=1);

namespace Netric\EntitySync\Collection;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\EntitySync\Commit\CommitManagerFactory;
use Netric\EntitySync\DataMapperFactory;
use Netric\EntitySync\EntitySync;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\WorkerMan\WorkerServiceFactory;
use Netric\EntityGroupings\DataMapper\EntityGroupingDataMapperFactory;

/**
 * Create a Grouping Collection service
 */
class GroupingCollectionFactory implements FactoryInterface
{
    /**
     * Construct an instance of this factory so we can inject it as a dependency
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $commitManager = $serviceLocator->get(CommitManagerFactory::class);
        $workerService = $serviceLocator->get(WorkerServiceFactory::class);
        $collectionDataMapper = $serviceLocator->get(CollectionDataMapperFactory::class);
        $groupingDataMapper = $serviceLocator->get(EntityGroupingDataMapperFactory::class);

        return new GroupingCollection($commitManager, $workerService, $collectionDataMapper, $groupingDataMapper);
    }
}
