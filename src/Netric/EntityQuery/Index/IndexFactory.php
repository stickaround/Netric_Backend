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
use Netric\ServiceManager;

/**
 * Create a EntityQuery Index service
 */
class IndexFactory implements ServiceManager\AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return IndexInterface
     */
    public function createService(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $serviceManager = $sl->getAccount()->getServiceManager();
        $database = $serviceManager->get(RelationalDbFactory::class);
        $entityFactory = $serviceManager->get(EntityFactoryFactory::class);
        $entityDefinitionLoader = $serviceManager->get(EntityDefinitionLoaderFactory::class);
        $entityLoader = $serviceManager->get(EntityLoaderFactory::class);        
        $currentUser = $sl->getAccount()->getAuthenticatedUser();

        return new EntityQueryIndexRdb(
            $database,
            $entityFactory,
            $entityDefinitionLoader,
            $entityLoader,
            $currentUser, // User that is currently logged in
            $serviceManager // ServiceManager for EntityQuery Plugin
        );
    }
}
