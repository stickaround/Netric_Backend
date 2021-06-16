<?php

namespace Netric\Entity;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityGroupings\GroupingLoaderFactory;

/**
 * Factory for constructing an activity log service
 */
class ActivityLogFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return ActivityLog
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $groupingsLoader = $serviceLocator->get(GroupingLoaderFactory::class);
        $log = $serviceLocator->getApplication()->getLog();

        return new ActivityLog($log, $entityLoader, $groupingsLoader);
    }
}
