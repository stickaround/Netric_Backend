<?php

/**
 * Service factory for the Entity Groupings Loader
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */

namespace Netric\EntityGroupings;

use Netric\Cache\CacheFactory;
use Netric\EntityGroupings\DataMapper\EntityGroupingDataMapperFactory;
use Netric\ServiceManager;
use Netric\ServiceManager\AccountServiceManagerInterface;

/**
 * Create a Grouping Loader service
 */
class GroupingLoaderFactory implements ServiceManager\AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return GroupingLoader
     */
    public function createService(AccountServiceManagerInterface $sl)
    {
        $dm = $sl->get(EntityGroupingDataMapperFactory::class);
        $cache = $sl->get(CacheFactory::class);

        return new GroupingLoader($dm, $cache);
    }
}
