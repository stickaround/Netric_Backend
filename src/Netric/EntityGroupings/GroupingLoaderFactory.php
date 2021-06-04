<?php

/**
 * Service factory for the Entity Groupings Loader
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */

namespace Netric\EntityGroupings;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Cache\CacheFactory;
use Netric\EntityGroupings\DataMapper\EntityGroupingDataMapperFactory;

/**
 * Create a Grouping Loader service
 */
class GroupingLoaderFactory implements use Aereus\ServiceContainer\FactoryInterface;

{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return GroupingLoader
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $dm = $serviceLocator->get(EntityGroupingDataMapperFactory::class);
        $cache = $serviceLocator->get(CacheFactory::class);

        return new GroupingLoader($dm, $cache);
    }
}
