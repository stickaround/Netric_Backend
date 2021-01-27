<?php

/**
 * Service factory for the Entity Groupings Loader
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */

namespace Netric\EntityGroupings;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Cache\CacheFactory;
use Netric\EntityGroupings\DataMapper\EntityGroupingDataMapperFactory;

/**
 * Create a Grouping Loader service
 */
class GroupingLoaderFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return GroupingLoader
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $dm = $serviceLocator->get(EntityGroupingDataMapperFactory::class);
        $cache = $serviceLocator->get(CacheFactory::class);

        return new GroupingLoader($dm, $cache);
    }
}
