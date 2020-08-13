<?php

namespace NetricTest\WorkerMan;

use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Aereus\Config\Config;
use Netric\Config\ConfigFactory;

/**
 * @group integration
 */
class ConfigFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $this->assertInstanceOf(
            Config::class,
            $sm->get(ConfigFactory::class)
        );
    }
}
