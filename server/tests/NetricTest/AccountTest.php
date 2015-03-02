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
}