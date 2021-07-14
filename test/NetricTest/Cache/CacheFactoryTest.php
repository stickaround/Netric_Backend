<?php

namespace NetricTest\Cache;

use Netric;
use NetricTest\Bootstrap;
use Netric\Cache\RedisCache;
use Netric\Cache\CacheFactory;

use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
class CacheFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            RedisCache::class,
            $sm->get(CacheFactory::class)
        );
    }
}
