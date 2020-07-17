<?php

namespace Netric\Permissions;

use Netric\ServiceManager;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityGroupings\GroupingLoaderFactory;

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
        $groupingLoader = $sl->get(GroupingLoaderFactory::class);
        return new DaclLoader($entityLoader, $groupingLoader);
    }
}
