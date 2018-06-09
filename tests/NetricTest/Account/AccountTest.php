<?php

/**
 * Test core netric application class
 */
namespace NetricTest\Account;

use Netric;
use NetricTest\Bootstrap;
use PHPUnit\Framework\TestCase;

class AccountTest extends TestCase
{
    public function testGetServiceManager()
    {
        $account = Bootstrap::getAccount();

        $this->assertInstanceOf('Netric\ServiceManager\ServiceLocatorInterface', $account->getServiceManager());
    }

    public function testGetApplication()
    {
        $account = Bootstrap::getAccount();
        $this->assertInstanceOf('Netric\Application\Application', $account->getApplication());
    }

    public function testGetAccountUrl()
    {
        $account = Bootstrap::getAccount();
        $config = $account->getServiceManager()->get("Config");

        // Test without protocol
        $url = $account->getAccountUrl(false);
        $this->assertEquals($account->getName() . "." . $config->localhost_root, $url);

        // Include the protocol
        $url = $account->getAccountUrl();
        $this->assertContains("http", $url);
    }
}
