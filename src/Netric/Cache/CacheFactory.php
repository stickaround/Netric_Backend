<?php
/**
 * Service factory for the Cache
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Cache;

use Netric\ServiceManager;
use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\ServiceManager\AccountServiceFactoryInterface;

/**
 * Create a Cache service
 */
class CacheFactory implements AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return CacheInterface
     */
    public function createService(AccountServiceManagerInterface $sl)
    {
        return $sl->getAccount()->getApplication()->getCache();
    }
}
