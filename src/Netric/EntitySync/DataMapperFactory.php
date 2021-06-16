<?php

declare(strict_types=1);

namespace Netric\EntitySync;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\EntitySync\Collection\CollectionFactoryFactory;
use Netric\Db\Relational\RelationalDbContainerFactory;
use Netric\WorkerMan\WorkerServiceFactory;
use Netric\ServiceManager;

/**
 * Create a Entity Sync Commit DataMapper service
 */
class DataMapperFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return DataMapperInterface
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $relationalDbCon = $serviceLocator->get(RelationalDbContainerFactory::class);
        $collectionFactory = $serviceLocator->get(CollectionFactoryFactory::class);
        $workerService = $serviceLocator->get(WorkerServiceFactory::class);

        return new DataMapperRdb($relationalDbCon, $workerService, $collectionFactory);
    }
}
