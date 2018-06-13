<?php

namespace NetricTest\Cache;

use Netric;

use PHPUnit\Framework\TestCase;

class CacheFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            'Netric\Cache\MemcachedCache',
            $sm->get('Cache')
        );

        $this->assertInstanceOf(
            'Netric\Cache\MemcachedCache',
            $sm->get('Netric\Cache\Cache')
        );
    }
}
