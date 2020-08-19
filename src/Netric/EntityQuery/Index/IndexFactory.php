<?php
/**
 * Service factory for the EntityQuery Index
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\EntityQuery\Index;

use Netric\Db\Relational\RelationalDbFactory;
use Netric\Entity\EntityFactoryFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\Db\Relational\RelationalDbContainerFactory;
use Netric\ServiceManager\AccountServiceFactoryInterface;
use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\ServiceManager;

/**
 * Create a EntityQuery Index service
 */
class IndexFactory implements AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param AccountServiceManagerInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return IndexInterface
     */
    public function createService(AccountServiceManagerInterface $serviceLocator)
    {
        $relationalDbCon = $serviceLocator->get(RelationalDbContainerFactory::class);        
        $entityFactory = $serviceLocator->get(EntityFactoryFactory::class);
        $entityDefinitionLoader = $serviceLocator->get(EntityDefinitionLoaderFactory::class);
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);        
        
        
        return new EntityQueryIndexRdb(
            $relationalDbCon,
            $entityFactory,
            $entityDefinitionLoader,
            $entityLoader,
            $serviceLocator
        );
    }
}
