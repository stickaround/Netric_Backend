<?php

/**
 * Make sure the bin/scripts/update/once/004/001/002.php script works
 */

namespace BinTest\Update\Once;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\EntityFactoryFactory;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Db\Relational\RelationalDbFactory;

use Netric\Console\BinScript;
use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
class Update005001002Test extends TestCase
{
    /**
     * Handle to account
     *
     * @var \Netric\Account\Account
     */
    private $account = null;

    /**
     * Path to the script to test
     *
     * @var string
     */
    private $scriptPath = null;

    /**
     * Test entities that should be cleaned up on tearDown
     *
     * @var EntityInterface[]
     */
    private $testEntities = [];

    /**
     * Setup each test
     */
    protected function setUp(): void
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $serviceManager = $this->account->getServiceManager();
        $this->entityLoader = $serviceManager->get(EntityLoaderFactory::class);
        $this->entityFactory = $serviceManager->get(EntityFactoryFactory::class);
        $this->db = $serviceManager->get(RelationalDbFactory::class);
        $this->scriptPath = __DIR__ . "/../../../../bin/scripts/update/once/005/001/002.php";
    }

    /**
     * Make sure the file exists
     *
     * This is more a test of the test to make sure we set the path right, but why
     * not just use unit tests for our tests? :)
     */
    public function testExists()
    {
        $this->assertTrue(file_exists($this->scriptPath), $this->scriptPath . " not found!");
    }

    /**
     * Cleanup after a test runs
     */
    protected function tearDown(): void
    {
        // Cleanup any test entities
        foreach ($this->testEntities as $entity) {
            $this->entityLoader->delete($entity, $this->account->getAuthenticatedUser());
        }
    }

    /**
     * At a basic level, make sure we can run without throwing any exceptions
     */
    public function testRun()
    {
        $entity = $this->entityFactory->create(ObjectTypes::NOTE, $this->account->getAccountId());
        $entityId = $this->entityLoader->save($entity, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $entity;

        // This entity should have sort_order value saved since it was set in the default 
        $entityWithSortOrder = $this->entityLoader->getEntityById($entityId, $this->account->getAccountId());
        $this->assertNotNull($entityWithSortOrder->getValue("sort_order"));

        // We need to manually set the sort_order field to null so we can test the sort_order value properly
        $this->db->query('UPDATE entity SET sort_order = NULL WHERE entity_id=:entityId', ["entityId" => $entityId]);

        $binScript = new BinScript($this->account->getApplication(), $this->account);
        $this->assertTrue($binScript->run($this->scriptPath));

        // After running the update script, this entity should have the sort_order value same as the ts_entered
        $savedEntity = $this->entityLoader->getEntityById($entityId, $this->account->getAccountId());
        $this->assertNotNull($savedEntity->getValue("sort_order"));
        $this->assertEquals($savedEntity->getValue("sort_order"), $entity->getValue("ts_entered"));
    }
}
