<?php
namespace Netric\Permissions;

use Netric\ServiceManager;
use Netric\EntityLoaderFactory;

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
        return new DaclLoader($entityLoader);
    }
}
