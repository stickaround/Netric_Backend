<?php
/**
 * Test core netric application class
 */
namespace NetricTest;

use Netric;
use PHPUnit_Framework_TestCase;

class ApplicationTest extends PHPUnit_Framework_TestCase
{
    public function testGetConfig()
    {
        $config = new Netric\Config();
        $app = new Netric\Application($config);
        
        $this->assertInstanceOf("Netric\Config", $app->getConfig());
    }
    
    /**
     * Test getting the current/default account
     */
    public function testGetAccount()
    {
        $config = new Netric\Config();
        $app = new Netric\Application($config);
        
        $this->assertInstanceOf("Netric\Account", $app->getAccount());
    }
}