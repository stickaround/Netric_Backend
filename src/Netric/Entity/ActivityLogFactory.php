<?php

namespace Netric\Entity;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityGroupings\GroupingLoaderFactory;

/**
 * Factory for constructing an activity log service
 */
class ActivityLogFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return ActivityLog
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $groupingsLoader = $serviceLocator->get(GroupingLoaderFactory::class);
        $log = $serviceLocator->getApplication()->getLog();

        return new ActivityLog($log, $entityLoader, $groupingsLoader);
    }
}
