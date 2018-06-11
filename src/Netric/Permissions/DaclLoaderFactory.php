<?php
namespace Netric\Permissions;

use Netric\ServiceManager;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;

/**
 * Create a DaclLoader
 */
class DaclLoaderFactory implements ServiceManager\ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceManager\ServiceLocatorInterface $sl ServiceLocator for injecting dependencies
     * @return DaclLoader
     */
    public function createService(ServiceManager\ServiceLocatorInterface $sl)
    {
        $entityLoader = $sl->get(EntityLoaderFactory::class);
        $entityDefinitionLoader = $sl->get(EntityDefinitionLoaderFactory::class);
        return new DaclLoader($entityLoader, $entityDefinitionLoader);
    }
}
