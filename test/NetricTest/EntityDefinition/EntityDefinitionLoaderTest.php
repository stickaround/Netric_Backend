<?php

namespace NetricTest\EntityDefinition;

use Netric\Cache\CacheInterface;
use Netric\EntityDefinition\DataMapper\EntityDefinitionDataMapperInterface;
use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityDefinition\EntityDefinitionLoader;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\EntityDefinition\ObjectTypes;

/**
 * Test entity definition loader class that is responsible for creating and initializing exisiting definitions
 */
class EntityDefinitionLoaderTest extends TestCase
{
    /**
     * Tennant account
     *
     * @var \Netric\Account\Account
     */
    private $account = null;

    /**
     * Setup each test
     */
    protected function setUp(): void
    {
        $this->account = Bootstrap::getAccount();
    }

    /**
     * Test loading an object definition
     */
    public function testGet()
    {
        $taskDefinition = new EntityDefinition(ObjectTypes::TASK, $this->account->getAccountId());
        $taskDefinition->id = 123;

        // Configure a mock DataMapper
        $dm = $this->getMockBuilder(EntityDefinitionDataMapperInterface::class)->getMock();
        //$dm->method('fetchByName')->willReturn($taskDefinition);
        $dm->method('save')->willReturn(true);
        $dm->method('getAccount')->willReturn($this->account);
        $dm->method('fetchByName')->willReturn($taskDefinition);

        // Configure a mock cache
        $cache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $cache->method('get')->willReturn(null);

        // Load the object through the loader which should cache it
        $loader = $this->account->getServiceManager()->get(EntityDefinitionLoaderFactory::class);
        $taskDefinitionLoaded = $loader->get(ObjectTypes::TASK, $this->account->getAccountId());

        $this->assertNotNull($taskDefinitionLoaded->getEntityDefinitionId());
    }

    // TODO: This is being skipped now because the cached definition was
    // still reloading all the aggregates which was massively slowing the system
    // down.
    //
    // /**
    //  * Test if object is being loaded from cache
    //  */
    // public function testGetCached()
    // {
    //     $taskDefinition = new EntityDefinition(ObjectTypes::TASK);

    //     // Configure a mock DataMapper
    //     $dm = $this->getMockBuilder(EntityDefinitionDataMapperInterface::class)->getMock();
    //     $dm->method('save')->willReturn(true);
    //     $dm->method('getAccount')->willReturn($this->account);

    //     // Make sure that fetchByName is skipped because the entity is in cache (below)
    //     $dm->expects($this->never())->method('fetchByName');

    //     // Configure a mock cache
    //     $cache = $this->getMockBuilder(CacheInterface::class)->getMock();
    //     $cache->method('get')->willReturn($taskDefinition);

    //     // Load the object through the loader which should cache it
    //     $loader = new EntityDefinitionLoader($dm, $cache);
    //     $loader->get(ObjectTypes::TASK);
    // }

    /**
     * Test loading all entity definitions
     */
    public function testGetAll()
    {
        $taskDefinition = new EntityDefinition(ObjectTypes::TASK, $this->account->getAccountId());
        $taskDefinition->id = 123;

        // Configure a mock DataMapper
        $dm = $this->getMockBuilder(EntityDefinitionDataMapperInterface::class)->getMock();
        $dm->method('getAccount')->willReturn($this->account);
        $dm->method('getAllObjectTypes')->willReturn([ObjectTypes::TASK]);
        $dm->method('fetchByName')->willReturn($taskDefinition);

        // Configure a mock cache
        $cache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $cache->method('get')->willReturn(null);

        // Load the object through the loader which should cache it
        $loader = $this->account->getServiceManager()->get(EntityDefinitionLoaderFactory::class);
        $allDefinitions = $loader->getAll($this->account->getAccountId());

        $this->assertGreaterThanOrEqual(1, count($allDefinitions));
    }
}
