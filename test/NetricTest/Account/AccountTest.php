<?php

/**
 * Test core netric application class
 */
namespace NetricTest\Account;

use Netric;
use NetricTest\Bootstrap;
use PHPUnit\Framework\TestCase;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Application\Application;
use Netric\Config\ConfigFactory;

class AccountTest extends TestCase
{
    public function testGetServiceManager()
    {
        $account = Bootstrap::getAccount();

        $this->assertInstanceOf(ServiceLocatorInterface::class, $account->getServiceManager());
    }

    public function testGetApplication()
    {
        $account = Bootstrap::getAccount();
        $this->assertInstanceOf(Application::class, $account->getApplication());
    }

    public function testGetAccountUrl()
    {
        $account = Bootstrap::getAccount();
        $config = $account->getServiceManager()->get(ConfigFactory::class);

        // Test without protocol
        $url = $account->getAccountUrl(false);
        $this->assertEquals($account->getName() . "." . $config->localhost_root, $url);

        // Include the protocol
        $url = $account->getAccountUrl();
        $this->assertStringContainsString("http", $url);
    }
}
