<?php

declare(strict_types=1);

namespace Netric\EntitySync;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\EntitySync\Collection\CollectionFactoryFactory;
use Netric\Db\Relational\RelationalDbContainerFactory;
use Netric\WorkerMan\WorkerServiceFactory;
//use Netric\ServiceManager; I think no need to import this. comment out for now

/**
 * Create a Entity Sync Commit DataMapper service
 */
class DataMapperFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return DataMapperInterface
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $relationalDbCon = $serviceLocator->get(RelationalDbContainerFactory::class);
        $collectionFactory = $serviceLocator->get(CollectionFactoryFactory::class);
        $workerService = $serviceLocator->get(WorkerServiceFactory::class);

        return new DataMapperRdb($relationalDbCon, $workerService, $collectionFactory);
    }
}
