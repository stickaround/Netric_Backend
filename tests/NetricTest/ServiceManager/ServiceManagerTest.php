<?php

/**
 * Test entity definition loader class that is responsible for creating and initializing exisiting definitions
 */
namespace NetricTest\ServiceManager;

use Netric;
use PHPUnit\Framework\TestCase;
use Netric\ServiceManager\Test\Service;
use Netric\ServiceManager\Test\ServiceFactory;
use Netric\Config\Config;
use Netric\Entity\ObjType\UserEntity;

class ServiceManagerTest extends TestCase
{
    /**
     * Handle to account
     *
     * @var \Netric\Account\Account
     */
    private $account = null;

    /**
     * Setup each test
     */
    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $this->user = $this->account->getUser(UserEntity::USER_SYSTEM);
    }

    /**
     * Load a service by service name and let the locator append 'Factory'
     */
    public function testGetByFactoryMissingFactory()
    {
        $sl = $this->account->getServiceManager();
        $svc = $sl->get(Service::class);
        $this->assertInstanceOf(Service::class, $svc);
        $this->assertEquals("TEST", $svc->getTestString());
    }

    /**
     * Load a service by it's factory name
     */
    public function testGetByFactoryFull()
    {
        $sl = $this->account->getServiceManager();
        $svc = $sl->get(ServiceFactory::class);
        $this->assertInstanceOf(Service::class, $svc);
        $this->assertEquals("TEST", $svc->getTestString());
    }

    /**
     * Make sure once a service is loaded it stays in memory
     */
    public function testIsloaded()
    {
        $sl = $this->account->getServiceManager();
        $testService1 = $sl->get(ServiceFactory::class);
        $this->assertTrue($sl->isLoaded(ServiceFactory::class));
        $testService2 = $sl->get(ServiceFactory::class);
        $this->assertSame($testService1, $testService2);
    }

    /**
     * Make sure that mapped or aliased services can be loaded
     */
    public function testGetMapped()
    {
        // "test" should map to "Netric/ServiceManager/Test/Service"
        $sl = $this->account->getServiceManager();
        $svc = $sl->get("test");
        $this->assertInstanceOf(Service::class, $svc);
        $this->assertEquals("TEST", $svc->getTestString());
    }

    /**
     * Check if we can get a service from the parent service locator
     *
     * Config is a member of the Application service locator, not the Account
     * so thye application locator will check the parent first.
     */
    public function testGetServiceFromParent()
    {
        $appSl = $this->account->getApplication()->getServiceManager();
        $accSl = $this->account->getServiceManager();

        // Get config service
        $appConfig = $appSl->get(Config::class);
        $this->assertInstanceOf(Config::class, $appConfig);

        // Now try loading it from the account service locator, with the alias
        $accConfig = $accSl->get("Config");
        $this->assertInstanceOf(Config::class, $accConfig);

        // Make sure they are the same
        $this->assertSame($appConfig, $accConfig);
    }
}
