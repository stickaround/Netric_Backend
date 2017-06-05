<?php
/**
 * Test entity definition loader class that is responsible for creating and initializing exisiting definitions
 */
namespace NetricTest\ServiceManager;

use Netric;
use PHPUnit\Framework\TestCase;

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
        $this->user = $this->account->getUser(\Netric\Entity\ObjType\UserEntity::USER_SYSTEM);
	}

    /**
     * Load a service by full namespace
     */
    public function testGetByFactory()
    {
        $sl = $this->account->getServiceManager();
        $svc = $sl->get("Netric/ServiceManager/Test/Service");
        $this->assertInstanceOf('\Netric\ServiceManager\Test\Service', $svc);
        $this->assertEquals("TEST", $svc->getTestString());
    }

    /**
     * Make sure that mapped or aliased services can be loaded
     */
    public function testGetMapped()
    {
        // "test" should map to "Netric/ServiceManager/Test/Service"
        $sl = $this->account->getServiceManager();
        $svc = $sl->get("test");
        $this->assertInstanceOf('\Netric\ServiceManager\Test\Service', $svc);
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
		$appConfig = $appSl->get("Netric/Config/Config");
		$this->assertInstanceOf("Netric\\Config\\Config", $appConfig);

		// Now try loading it from the account service locator, with the alias
		$accConfig = $accSl->get("Config");
		$this->assertInstanceOf("Netric\\Config\\Config", $accConfig);

		// Make sure they are the same
		$this->assertSame($appConfig, $accConfig);
	}
}
