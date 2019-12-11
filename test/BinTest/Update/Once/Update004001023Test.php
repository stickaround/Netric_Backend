<?php
/**
 * Make sure the bin/scripts/update/once/004/001/023.php script works
 */
namespace BinTest\Update\Once;

use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\Db\Relational\RelationalDbFactory;
use Netric\Authentication\AuthenticationServiceFactory;
use Netric\Entity\DataMapper\DataMapperFactory as EntityDataMapperFactory;
use Netric\Console\BinScript;
use PHPUnit\Framework\TestCase;
use Netric\EntityDefinition\ObjectTypes;

class Update004001023Test extends TestCase
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
     * Ids of old user entities that should be cleaned up on tearDown
     *
     * @var Int[]
     */
    private $testOldUserIds = [];

    /**
     * Tests moved objects that should be cleaned up on tearDown
     *
     * @var Array[]
     */
    private $testMovedObjects = [];

    /**
     * Setup each test
     */
    protected function setUp(): void
{
        $this->account = \NetricTest\Bootstrap::getAccount();
        $this->scriptPath = __DIR__ . "/../../../../bin/scripts/update/once/004/001/023.php";
    }

    /**
     * Cleanup after a test runs
     */
    protected function tearDown(): void
{
        $serviceManager = $this->account->getServiceManager();
        $loader = $serviceManager->get(EntityLoaderFactory::class);
        $db = $serviceManager->get(RelationalDbFactory::class);

        // Cleanup any test entities
        foreach ($this->testEntities as $entity) {
            $loader->delete($entity, true);
        }

        // Cleanup old user entities
        foreach ($this->testOldUserIds as $userMovedToId) {
            $db->delete("users", ["id" => $userMovedToId]);
        }

        // Cleanup the moved objects
        foreach ($this->testMovedObjects as $movedObjectsData) {
            $db->delete("objects_moved", $movedObjectsData);
        }
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
     * At a basic level, make sure we can run without throwing any exceptions
     */
    public function testRun()
    {
        $serviceManager = $this->account->getServiceManager();
        $entityLoader = $serviceManager->get(EntityLoaderFactory::class);
        $entityDefinitionLoader = $serviceManager->get(EntityDefinitionLoaderFactory::class);
        $entityDataMapper = $serviceManager->get(EntityDataMapperFactory::class);
        $db = $serviceManager->get(RelationalDbFactory::class);

        // Create a user entity that will act as our moved to user entity
        $userMovedTo = $entityLoader->create(ObjectTypes::USER);
        $userMovedTo->setValue("name", "Unit Test Moved To User");
        $userMovedToId = $entityDataMapper->save($userMovedTo);
        $this->testEntities[] = $userMovedTo;

        // Create a user entity that will act as our moved from user entity
        $userMovedFrom = $entityLoader->create(ObjectTypes::USER);
        $userMovedFrom->setValue("name", "Unit Test Moved From User");
        $userMovedFromId = $entityDataMapper->save($userMovedFrom);
        $this->testEntities[] = $userMovedFrom;

        // Create a task entity and set the $userMovedFrom as our user_id
        $task = $entityLoader->create(ObjectTypes::TASK);
        $task->setValue("name", "Referenced Entity for User Moved From");
        $task->setValue("user_id", $userMovedFromId);
        $taskId = $entityDataMapper->save($task);
        $this->testEntities[] = $task;

        // Create a user entity in the old users table
        $oldUserId = $db->insert("users", ["name" => "Unit Test Old User", "active" => true]);
        $this->testOldUserIds[] = $oldUserId;

        // Create a task entity and add the old user as it's owner
        $taskOldUserRef = $entityLoader->create(ObjectTypes::TASK);
        $taskOldUserRef->setValue("name", "Referenced Entity for old user entity");
        $taskOldUserRefId = $entityDataMapper->save($taskOldUserRef);
        $this->testEntities[] = $taskOldUserRef;

        /*
         * We need to manually update the task entity to set the old user id
         * because entityDataMapper is checking the foreign tables before setting a referenced entity
         */
        $db->update('objects_task', ["user_id" => $oldUserId], ["id" => $taskOldUserRefId]);

        /*
         * Manually add the moved user in objects_moved table so it will not execute the updateOldReferences() function
         * in the EntityRdbDataMapper::setEntityMovedTo() function
         */
        $movedData = [
            'object_type_id' => $userMovedTo->getDefinition()->getId(),
            'object_id' => $userMovedFromId,
            'moved_to' => $userMovedToId,
        ];
        $db->insert('objects_moved', $movedData);
        $this->testMovedObjects[] = $movedData;

        $movedDataOldUser = [
            'object_type_id' => $userMovedTo->getDefinition()->getId(),
            'object_id' => $oldUserId,
            'moved_to' => $userMovedToId,
        ];
        $db->insert('objects_moved', $movedDataOldUser);
        $this->testMovedObjects[] = $movedDataOldUser;

        // Run the 023.php update once script to scan the objects_moved table and update the referenced entities
        $binScript = new BinScript($this->account->getApplication(), $this->account);
        $this->assertTrue($binScript->run($this->scriptPath));

        // Load the task entity and it should update the moved from user_id to moved to user_id
        $entityLoader->clearCache(ObjectTypes::TASK, $taskId);
        $taskEntity = $entityLoader->get(ObjectTypes::TASK, $taskId);
        $this->assertEquals($userMovedToId, $taskEntity->getValue("user_id"));
    }
}