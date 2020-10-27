<?php

/**
 * Test entity  loader class that is responsible for creating and initializing exisiting objects
 */

namespace NetricTest\Entity;

use Netric\Cache\CacheFactory;
use Netric\Entity\DataMapper\EntityDataMapperInterface;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
use PHPUnit\Framework\TestCase;
use Netric\Entity\EntityFactoryFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\DataMapper\EntityDataMapperFactory;
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
            $loader->delete($entity, $this->account->getAuthenticatedUser());
        }
    }

    /**
     * Test loading an object definition
     */
    public function testGetByGuid()
    {
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);

        // Create a test object
        $dataMapper = $this->account->getServiceManager()->get(EntityDataMapperFactory::class);
        $cust = $loader->create(ObjectTypes::CONTACT, $this->account->getAccountId());
        $cust->setValue("name", "EntityLoaderTest:testGet");
        $dataMapper->save($cust, $this->account->getAuthenticatedUser());

        // Use the laoder to get the object
        $ent = $loader->getEntityById($cust->getEntityId(), $this->account->getAccountId());
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
        $dataMapper->delete($cust, $this->account->getAuthenticatedUser());
    }

    public function testByUniqueName()
    {
        $entityFactory = $this->account->getServiceManager()->get(EntityFactoryFactory::class);
        $task = $entityFactory->create(ObjectTypes::TASK, $this->account->getAccountId());

        // Configure a mock datamapper
        $dataMapper = $this->getMockBuilder(EntityDataMapperInterface::class)->getMock();
        $dataMapper->method('getByUniqueName')
            ->willReturn($task);

        $entityFactory = $this->account->getServiceManager()->get(EntityFactoryFactory::class);
        $cache = $this->account->getServiceManager()->get(CacheFactory::class);
        $defLoader = $this->account->getServiceManager()->get(EntityDefinitionLoaderFactory::class);
        $loader = new EntityLoader($dataMapper, $defLoader, $entityFactory, $cache);

        $entity = $loader->getByUniqueName(ObjectTypes::TASK, "my_test", $this->account->getAccountId());

        $this->assertEquals($task, $entity);
    }

    /**
     * Test entity has moved functionalty
     */
    public function testSetEntityMovedTo()
    {
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);

        // Create first entity
        $customer = $loader->create(ObjectTypes::CONTACT, $this->account->getAccountId());
        $customer->setValue("name", "testSetEntityMovedTo");
        $oid1 = $loader->save($customer, $this->account->getAuthenticatedUser());

        // Queue for cleanup
        $this->testEntities[] = $customer;

        // Create second entity
        $customer2 = $loader->create(ObjectTypes::CONTACT, $this->account->getAccountId());
        $customer2->setValue("name", "testSetEntityMovedTo");
        $oid2 = $loader->save($customer2, $this->account->getAuthenticatedUser());

        // Queue for cleanup
        $this->testEntities[] = $customer2;

        // Set moved to
        $def = $customer->getDefinition();
        $ret = $loader->setEntityMovedTo(
            $customer->getEntityId(),
            $customer2->getEntityId(),
            $this->account->getAccountId()
        );
        $this->assertTrue($ret);
    }
}
