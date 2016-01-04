<?php
/**
 * Test core netric application class
 */
namespace ApplicationTest;

use Netric;
use PHPUnit_Framework_TestCase;

class AccountTest extends PHPUnit_Framework_TestCase
{
    public function testGetServiceManager()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        
        $this->assertInstanceOf('Netric\ServiceManager\ServiceLocatorInterface', $account->getServiceManager());
    }
    
    public function testGetApplication()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $this->assertInstanceOf('Netric\Application', $account->getApplication());
    }

    public function testGetAccountUrl()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $config = $account->getServiceManager()->get("Config");

        // Test without protocol
        $url = $account->getAccountUrl(false);
        $this->assertEquals($account->getName() . "." . $config->localhost_root, $url);

        // Include the protocol
        $url = $account->getAccountUrl();
        $this->assertContains("http", $url);
    }
}