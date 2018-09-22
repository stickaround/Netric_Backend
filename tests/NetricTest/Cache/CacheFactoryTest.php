<?php

namespace NetricTest\Cache;

use Netric;
use NetricTest\Bootstrap;
use Netric\Cache\MemcachedCache;
use Netric\Cache\CacheFactory;

use PHPUnit\Framework\TestCase;

class CacheFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            MemcachedCache::class,
            $sm->get(CacheFactory::class)
        );
    }
}
