<?php
namespace NetricTest\WorkerMan;

use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Config\Config;
use Netric\Config\ConfigFactory;

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
