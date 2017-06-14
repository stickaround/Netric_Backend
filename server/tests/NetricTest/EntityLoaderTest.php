<?php
/**
 * Test entity  loader class that is responsible for creating and initializing exisiting objects
 */
namespace NetricTest;

use Netric\Entity\DataMapperInterface;
use Netric\Entity\EntityInterface;
use Netric\EntityLoader;
use PHPUnit\Framework\TestCase;

class EntityLoaderTest extends TestCase
{
	/**
     * Tennant account
     * 
     * @var \Netric\Account\Account
     */
    private $account = null;

    /**
     * Entities to delete on tearDown
     *
     * @var EntityInterface[]
     */
    private $testEntities = [];

	/**
	 * Setup each test
	 */
	protected function setUp() 
	{
        $this->account = \NetricTest\Bootstrap::getAccount();
	}

    /**
     * Cleanup any test entities
     */
	protected function tearDown()
    {
        $loader = $this->account->getServiceManager()->get("EntityLoader");
        foreach ($this->testEntities as $entity) {
            $loader->delete($entity, true);
        }
    }

    /**
	 * Test loading an object definition
	 */
	public function testGet()
	{
		$loader = $this->account->getServiceManager()->get("EntityLoader");

		// Create a test object
		$dm = $this->account->getServiceManager()->get("Entity_DataMapper");
		$cust = $loader->create("customer");
		$cust->setValue("name", "EntityLoaderTest:testGet");
		$cid = $dm->save($cust);
		
		// Use the laoder to get the object
		$ent = $loader->get("customer", $cid);
		$this->assertEquals($cust->getId(), $ent->getId());

		// Test to see if the isLoaded function indicates the entity has been loaded and cached locally
		$refIm = new \ReflectionObject($loader);
        $isLoaded = $refIm->getMethod("isLoaded");
		$isLoaded->setAccessible(true);
		$this->assertTrue($isLoaded->invoke($loader, "customer", $cid));

		// Test to see if it is cached
		$refIm = new \ReflectionObject($loader);
        $getCached = $refIm->getMethod("getCached");
		$getCached->setAccessible(true);
		$this->assertTrue(is_array($getCached->invoke($loader, "customer", $cid)));

		// Cleanup
		$dm->delete($cust, true);
	}

	public function testByUniqueName()
    {
        $entityFactory = $this->account->getServiceManager()->get("Netric/Entity/EntityFactory");
        $task = $entityFactory->create("task");


        // Configure a mock datamapper
        $dm = $this->getMockBuilder(DataMapperInterface::class)->getMock();;
        $dm->method('getByUniqueName')
            ->willReturn($task);
        $dm->method('getAccount')
            ->willReturn($this->account);

        $defLoader = $this->account->getServiceManager()->get("Netric/EntityDefinitionLoader");
        $loader = new EntityLoader($dm, $defLoader);

        $entity = $loader->getByUniqueName("task", "my_test");

        $this->assertEquals($task, $entity);
    }

    /**
     * Reload makes sure than an entity is refreshed with the latest version
     */
    public function testReload()
    {
        $loader = $this->account->getServiceManager()->get("EntityLoader");
        $dataMapper = $this->account->getServiceManager()->get("Entity_DataMapper");
        $entityFactory = $this->account->getServiceManager()->get("Netric/Entity/EntityFactory");

        // Create a new entity which will save it in cache
        $task1 = $entityFactory->create("task");
        $task1->setValue("name", 'test_reload');
        $loader->save($task1);
        $this->testEntities[] = $task1; // cleanup

        // Now save changes directly to the database bypassing the cache
        $task2 = $entityFactory->create("task");
        $dataMapper->getById($task2, $task1->getId());
        $task2->setValue("name", "test_reload_edited");
        $dataMapper->save($task2);

        // First assert that they are different
        $this->assertNotEquals($task2->getValue("name"), $task1->getValue("name"));

        // Now reload task 1
        $loader->reload($task1);

        // Make sure the values match what was in the database
        $this->assertEquals($task2->getValue("name"), $task1->getValue("name"));
    }
}
