<?php
namespace Netric\EntityGroupings\DataMapper;

use Netric\Db\Relational\RelationalDbContainerFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\ServiceManager\AccountServiceFactoryInterface;
use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\EntitySync\Commit\CommitManagerFactory;
use Netric\EntitySync\EntitySyncFactory;

/**
 * Create a EntityGroupings DataMapper service
 */
class EntityGroupingDataMapperFactory implements AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param AccountServiceManagerInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return EntityGroupingRdbDataMapper
     */
    public function createService(AccountServiceManagerInterface $serviceLocator)
    {
        $relationalDbCon = $serviceLocator->get(RelationalDbContainerFactory::class);
        $entityDefinitionLoader = $serviceLocator->get(EntityDefinitionLoaderFactory::class);
        $commitManager = $serviceLocator->get(CommitManagerFactory::class);
        $entitySync = $serviceLocator->get(EntitySyncFactory::class);        

        return new EntityGroupingRdbDataMapper(
            $relationalDbCon,
            $entityDefinitionLoader,
            $commitManager,
            $entitySync
        );
    }
}
