<?php
namespace NetricTest\EntityDefinition;

use Netric\Cache\CacheInterface;
use Netric\EntityDefinition\DataMapper\EntityDefinitionDataMapperInterface;
use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityDefinition\EntityDefinitionLoader;
use PHPUnit\Framework\TestCase;

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
    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
    }

    /**
     * Test loading an object definition
     */
    public function testGet()
    {
        $taskDefinition = new EntityDefinition('task');
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
        $loader = new EntityDefinitionLoader($dm, $cache);
        $taskDefinitionLoaded = $loader->get("task");

        $this->assertSame($taskDefinition, $taskDefinitionLoaded);
    }

    /**
     * Test if object is being loaded from cache
     */
    public function testGetCached()
    {
        $taskDefinition = new EntityDefinition('task');

        // Configure a mock DataMapper
        $dm = $this->getMockBuilder(EntityDefinitionDataMapperInterface::class)->getMock();
        $dm->method('save')->willReturn(true);
        $dm->method('getAccount')->willReturn($this->account);

        // Make sure that fetchByName is skipped because the entity is in cache (below)
        $dm->expects($this->never())->method('fetchByName');

        // Configure a mock cache
        $cache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $cache->method('get')->willReturn($taskDefinition);

        // Load the object through the loader which should cache it
        $loader = new EntityDefinitionLoader($dm, $cache);
        $loader->get("task");
    }

    /**
     * Test loading all entity definitions
     */
    public function testGetAll()
    {
        $taskDefinition = new EntityDefinition('task');
        $taskDefinition->id = 123;

        // Configure a mock DataMapper
        $dm = $this->getMockBuilder(EntityDefinitionDataMapperInterface::class)->getMock();
        $dm->method('getAccount')->willReturn($this->account);
        $dm->method('getAllObjectTypes')->willReturn(['task']);
        $dm->method('fetchByName')->willReturn($taskDefinition);

        // Configure a mock cache
        $cache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $cache->method('get')->willReturn(null);

        // Load the object through the loader which should cache it
        $loader = new EntityDefinitionLoader($dm, $cache);
        $allDefinitions = $loader->getAll();

        $this->assertEquals([$taskDefinition], $allDefinitions);
    }
}
