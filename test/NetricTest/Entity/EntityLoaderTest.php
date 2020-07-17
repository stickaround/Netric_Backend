<?php

/**
 * Test entity  loader class that is responsible for creating and initializing exisiting objects
 */

namespace NetricTest\Entity;

use Netric\Cache\CacheFactory;
use Netric\Entity\DataMapperInterface;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
use PHPUnit\Framework\TestCase;
use Netric\Entity\EntityFactoryFactory;
use Netric\Cache\CacheInterface;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\DataMapper\DataMapperFactory;
use Netric\EntityDefinition\ObjectTypes;
use NetricTest\Bootstrap;
use Ramsey\Uuid\Uuid;

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
    protected function setUp(): void
    {
        $this->account = Bootstrap::getAccount();
    }

    /**
     * Cleanup any test entities
     */
    protected function tearDown(): void
    {
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        foreach ($this->testEntities as $entity) {
            $loader->delete($entity, true);
        }
    }

    /**
     * Test loading an object definition
     */
    public function testGetByGuid()
    {
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);

        // Create a test object
        $dataMapper = $this->account->getServiceManager()->get(DataMapperFactory::class);
        $cust = $loader->create(ObjectTypes::CONTACT);
        $cust->setValue("name", "EntityLoaderTest:testGet");
        $dataMapper->save($cust);

        // Use the laoder to get the object
        $ent = $loader->getByGuid($cust->getEntityId());
        $this->assertEquals($cust->getEntityId(), $ent->getEntityId());

        // Test to see if the isLoaded function indicates the entity has been loaded and cached locally
        $refIm = new \ReflectionObject($loader);
        $isLoaded = $refIm->getMethod("isLoaded");
        $isLoaded->setAccessible(true);
        $this->assertTrue($isLoaded->invoke($loader, $cust->getEntityId()));

        // Test to see if it is cached
        $refIm = new \ReflectionObject($loader);
        $getCached = $refIm->getMethod("getCached");
        $getCached->setAccessible(true);
        $this->assertTrue(is_array($getCached->invoke($loader, $cust->getEntityId())));

        // Cleanup
        $dataMapper->delete($cust, true);
    }

    public function testByUniqueName()
    {
        $entityFactory = $this->account->getServiceManager()->get(EntityFactoryFactory::class);
        $task = $entityFactory->create(ObjectTypes::TASK);

        // Configure a mock datamapper
        $dataMapper = $this->getMockBuilder(DataMapperInterface::class)->getMock();;
        $dataMapper->method('getByUniqueName')
            ->willReturn($task);
        $dataMapper->method('getAccount')
            ->willReturn($this->account);

        $entityFactory = $this->account->getServiceManager()->get(EntityFactoryFactory::class);
        $cache = $this->account->getServiceManager()->get(CacheFactory::class);
        $defLoader = $this->account->getServiceManager()->get(EntityDefinitionLoaderFactory::class);
        $loader = new EntityLoader($dataMapper, $defLoader, $entityFactory, $cache);

        $entity = $loader->getByUniqueName(ObjectTypes::TASK, "my_test");

        $this->assertEquals($task, $entity);
    }
}
