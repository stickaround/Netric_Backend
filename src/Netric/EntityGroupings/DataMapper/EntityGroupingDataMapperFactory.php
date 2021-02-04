<?php
namespace Netric\EntityGroupings\DataMapper;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Db\Relational\RelationalDbContainerFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\EntitySync\Commit\CommitManagerFactory;
use Netric\EntitySync\EntitySyncFactory;
use Netric\WorkerMan\WorkerServiceFactory;

/**
 * Create a EntityGroupings DataMapper service
 */
class EntityGroupingDataMapperFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return EntityGroupingRdbDataMapper
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $relationalDbCon = $serviceLocator->get(RelationalDbContainerFactory::class);
        $entityDefinitionLoader = $serviceLocator->get(EntityDefinitionLoaderFactory::class);
        $commitManager = $serviceLocator->get(CommitManagerFactory::class);
        $workerService = $serviceLocator->get(WorkerServiceFactory::class);

        return new EntityGroupingRdbDataMapper(
            $relationalDbCon,
            $entityDefinitionLoader,
            $commitManager,
            $workerService
        );
    }
}
