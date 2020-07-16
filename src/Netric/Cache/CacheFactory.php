<?php
/**
 * Service factory for the Cache
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Cache;

use Netric\ServiceManager\ServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Create a Cache service
 */
class CacheFactory implements ServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $sl ServiceLocator for injecting dependencies
     * @return CacheInterface
     */
    public function createService(ServiceLocatorInterface $sl)
    {
        return $sl->getApplication()->getCache();
    }
}
