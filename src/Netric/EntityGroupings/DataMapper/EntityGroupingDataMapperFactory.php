<?php
namespace Netric\EntityGroupings\DataMapper;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Db\Relational\RelationalDbContainerFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\EntitySync\Commit\CommitManagerFactory;
use Netric\EntitySync\EntitySyncFactory;
use Netric\WorkerMan\WorkerServiceFactory;

/**
 * Create a EntityGroupings DataMapper service
 */
class EntityGroupingDataMapperFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return EntityGroupingRdbDataMapper
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
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
