<?php
/**
 * Test core netric application class
 */
namespace NetricTest;

use Netric;
use PHPUnit_Framework_TestCase;

class ApplicationTest extends PHPUnit_Framework_TestCase
{
    /**
     * Application object to test
     *
     * @var Netric\Application
     */
    private $application = null;

    protected function setUp()
    {
        $config = new Netric\Config();
        $this->application = new Netric\Application($config);
    }

    public function testGetConfig()
    {
        $this->assertInstanceOf('Netric\Config', $this->application->getConfig());
    }
    
    /**
     * Test getting the current/default account
     */
    public function testGetAccount()
    {
        $this->assertInstanceOf('Netric\Account', $this->application->getAccount());
    }

    public function testGetAccountsByEmail()
    {
        // TODO: Add this test
    }

    public function testCreateAccount()
    {
        // TODO: Add this test
    }

    public function testUpdateAccount()
    {
        // TODO: Add this test
    }

    public function testDeleteAccount()
    {
        // TODO: Add this test
    }
}