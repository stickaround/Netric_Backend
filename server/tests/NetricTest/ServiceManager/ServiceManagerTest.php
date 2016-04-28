<?php
/**
 * Test entity definition loader class that is responsible for creating and initializing exisiting definitions
 */
namespace NetricTest\ServiceManager;

use Netric;
use PHPUnit_Framework_TestCase;

class ServiceManagerTest extends PHPUnit_Framework_TestCase 
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
        $this->user = $this->account->getUser(\Netric\Entity\ObjType\UserEntity::USER_ADMINISTRATOR);
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
	 * Check if we can get the config
	 */
	public function testGetLocalFactoryFunction()
	{
		$sl = $this->account->getServiceManager();

		// Get config service
		$config = $sl->get("Config");
		$this->assertInstanceOf("Netric\Config\Config", $config);

		// Test to see if the isLoaded function indicates the service has been loaded
		$refIm = new \ReflectionObject($sl);
        $isLoaded = $refIm->getMethod("isLoaded");
		$isLoaded->setAccessible(true);
		$this->assertTrue($isLoaded->invoke($sl, "Netric\Config\Config"));

		// Now that we know it is cached, lets make sure the returned object is correct
		$config = $sl->get("Config");
		$this->assertInstanceOf("Netric\Config\Config", $config);
	}
    
    /**
	 * Test getting entity datamapper
	 */
	public function testFactoryAntFs()
	{
		$sl = $this->account->getServiceManager();

		// Get config service
		$antfs = $sl->get("AntFs");
		$this->assertInstanceOf("\AntFs", $antfs);
	}
}
