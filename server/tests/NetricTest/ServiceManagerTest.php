<?php
/**
 * Test entity definition loader class that is responsible for creating and initializing exisiting definitions
 */
namespace NetricTest;

use Netric;
use PHPUnit_Framework_TestCase;

class ServiceManagerTest extends PHPUnit_Framework_TestCase 
{
    /**
     * Handle to account
     * 
     * @var \Netric\Account
     */
	private $account = null;

	/**
	 * Setup each test
	 */
	protected function setUp() 
	{
		$this->account = \NetricTest\Bootstrap::getAccount();
        $this->user = $this->account->getUser(\Netric\User::USER_ADMINISTRATOR);
	}

	/**
	 * Check if we can get the config
	 */
	public function testGet()
	{
		$sl = $this->account->getServiceManager();

		// Get config service
		$config = $sl->get("Config");
		$this->assertInstanceOf("Netric\Config", $config);

		// Test to see if the isLoaded function indicates the service has been loaded
		$refIm = new \ReflectionObject($sl);
        $isLoaded = $refIm->getMethod("isLoaded");
		$isLoaded->setAccessible(true);
		$this->assertTrue($isLoaded->invoke($sl, "Config"));

		// Now that we know it is cached, lets make sure the returned object is correct
		$config = $sl->get("Config");
		$this->assertInstanceOf("Netric\Config", $config);
	}

	/**
	 * Test getting entity datamapper
	 *
	public function testFactoryEntity_DataMapper()
	{
		$sl = $this->account->getServiceManager();

		// Get config service
		$config = $sl->get("Entity_DataMapper");
		$this->assertInstanceOf("Netric\Entity\DataMapper\Pgsql", $config);
	}
     * 
     */

	/**
	 * Test getting entity datamapper
	 */
	public function testFactoryEntityDefinition_DataMapper()
	{
		$sl = $this->account->getServiceManager();

		// Get config service
		$dm = $sl->get("EntityDefinition_DataMapper");
		$this->assertInstanceOf("Netric\EntityDefinition\DataMapper\Pgsql", $dm);
	}
    
    /**
	 * Test getting entity datamapper
	 */
	public function testFactoryEntityLoader()
	{
		$sl = $this->account->getServiceManager();

		// Get config service
		$config = $sl->get("EntityLoader");
		$this->assertInstanceOf("Netric\EntityLoader", $config);
	}
    
    /**
	 * Test getting groupings loader
	 */
	public function testFactoryEntityGroupings_Loader()
	{
		$sl = $this->account->getServiceManager();

		// Get config services
		$config = $sl->get("EntityGroupings_Loader");
		$this->assertInstanceOf("Netric\EntityGroupings\Loader", $config);
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
    
    /**
	 * Test getting logger
	 */
	public function testFactoryLog()
	{
		$sl = $this->account->getServiceManager();

		// Get config services
		$log = $sl->get("Log");
		$this->assertInstanceOf("Netric\Log", $log);
	}

	/**
	 * Test getting entity datamapper
	 */
	public function testEntityCommitManager()
	{
		$sl = $this->account->getServiceManager();

		// Get config service
		$manager = $sl->get("EntityCommitManager");
		$this->assertInstanceOf("Netric\\Entity\\Commit\\Manager", $manager);
	}
}
