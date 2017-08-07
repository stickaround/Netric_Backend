<?php
namespace NetricTest\Entity;

use PHPUnit\Framework\TestCase;
use Netric\Entity\EntityInterface;
use Netric\EntityDefinition\EntityDefinition;
use Netric\Entity\EntityMaintainerService;
use Netric\Log\LogInterface;
use Netric\Permissions\Dacl;
use Netric\EntityDefinitionLoader;
use Netric\FileSystem\FileSystem;

/**
 * Class EntityMaintainerServiceTest
 * @group integration
 */
class EntityMaintainerServiceTest extends TestCase
{
    /**
     * Test entity definition that should be cleaned up on tearDown
     *
     * @var EntityDefinition
     */
    private $testDefinition = null;

    /**
     * Test entities to delete on tearDown
     *
     * @var EntityInterface[]
     */
    private $testEntities = [];

    /**
     * Service to test
     *
     * @var EntityMaintainerService
     */
    private $maintainerService = null;

    /**
     * Setup test objects and data
     */
    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();

        // Create a temporary definition with a max cap of 1 entity
        $def = new EntityDefinition("utest_maint" . rand());
        $def->setTitle("Unit Test Maintenance");
        $def->capped = 1;
        $def->setSystem(false);
        $dacl = new Dacl();
        $def->setDacl($dacl);
        $dataMapper = $this->account->getServiceManager()->get("Netric/EntityDefinition/DataMapper/DataMapper");
        $dataMapper->saveDef($def);
        $this->testDefinition = $def;

        // Setup a mock definition loader since we don't want to test all definitions
        $entityDefinitionLoader = $this->getMockBuilder(EntityDefinitionLoader::class)
            ->disableOriginalConstructor()
            ->getMock();;
        $entityDefinitionLoader->method('getAll')->willReturn([$def]);

        $entityLoader = $this->account->getServiceManager()->get("EntityLoader");
        $entityIndex = $this->account->getServiceManager()->get("EntityQuery_Index");
        $fileSystem = $this->account->getServiceManager()->get(FileSystem::class);
        $log = $this->getMockBuilder(LogInterface::class)->getMock();
        $this->maintainerService = new EntityMaintainerService(
            $log,
            $entityLoader,
            $entityDefinitionLoader,
            $entityIndex,
            $fileSystem
        );
    }

    /**
     * Teardown test objects and data
     */
    protected function tearDown()
    {
        // Cleanup test entities
        $entityLoader = $this->account->getServiceManager()->get("EntityLoader");
        foreach ($this->testEntities as $ent) {
            $entityLoader->delete($ent, true);
        }

        // Cleanup the entity definition
        $dataMapper = $this->account->getServiceManager()->get("Netric/EntityDefinition/DataMapper/DataMapper");
        $dataMapper->deleteDef($this->testDefinition);
    }

    /**
     * Make sure that runAll actually runs all the cleanup tasks
     */
    public function testRunAll()
    {
        $allStats = $this->maintainerService->runAll();

        $this->assertArrayHasKey('trimmed', $allStats);
        $this->assertArrayHasKey('purged', $allStats);
        $this->assertArrayHasKey('deleted_spam', $allStats);
        $this->assertArrayHasKey('deleted_temp_files', $allStats);
    }

    /**
     * Make sure we can trim all capped object types
     */
    public function testTrimAllCappedTypes()
    {
        $entityLoader = $this->account->getServiceManager()->get("EntityLoader");

        // Create 2 entities which is one more than the cap
        $entity1 = $entityLoader->create($this->testDefinition->getObjType());
        $entityLoader->save($entity1);
        $this->testEntities[] = $entity1;

        $entity2 = $entityLoader->create($this->testDefinition->getObjType());
        $entityLoader->save($entity2);
        $this->testEntities[] = $entity2;

        // Run trimCappedForType
        $trimmed = $this->maintainerService->trimAllCappedTypes();

        // assert that the number of deleted is 1
        $this->assertEquals(1, count($trimmed[$this->testDefinition->getObjType()]));
    }

    /**
     * Make sure we can trim a specific object type
     */
    public function testTrimCappedForType()
    {
        $entityLoader = $this->account->getServiceManager()->get("EntityLoader");

        // Create 2 entities which is one more than the cap
        $entity1 = $entityLoader->create($this->testDefinition->getObjType());
        $entityLoader->save($entity1);
        $this->testEntities[] = $entity1;

        $entity2 = $entityLoader->create($this->testDefinition->getObjType());
        $entityLoader->save($entity2);
        $this->testEntities[] = $entity2;

        // Run trimCappedForType (getAll definitions will return only this->testDefinition)
        $trimmed = $this->maintainerService->trimCappedForType($this->testDefinition);

        // assert that the number of deleted is 1
        $this->assertEquals(1, count($trimmed));
    }

    /**
     * Make sure we can delete all old deleted entities
     */
    public function testPurgeAllStaleDeleted()
    {
        $entityLoader = $this->account->getServiceManager()->get("EntityLoader");

        // Create then soft delete two entities
        $entity1 = $entityLoader->create($this->testDefinition->getObjType());
        $entityLoader->save($entity1);
        $entityLoader->delete($entity1, false);

        // Get a cutoff
        $cutoff = new \DateTime();

        // Pause for a second to make sure the second entity is after the cutoff
        sleep(1);

        // This entity will be deleted after the cutoff so it should be left alone
        $entity2 = $entityLoader->create($this->testDefinition->getObjType());
        $entityLoader->save($entity2);
        $entityLoader->delete($entity2, false);
        $this->testEntities[] = $entity2;

        // Run trimCappedForType
        $purged = $this->maintainerService->purgeAllStaleDeleted($cutoff);

        // assert that the number of deleted is 1
        $this->assertEquals(1, count($purged[$this->testDefinition->getObjType()]));
    }

    /**
     * Make sure we can delete all old deleted entities
     */
    public function testPurgeStaleDeletedForType()
    {
        $entityLoader = $this->account->getServiceManager()->get("EntityLoader");

        // Create an entity to purge after deleted
        $entity1 = $entityLoader->create($this->testDefinition->getObjType());
        $entityLoader->save($entity1);
        $entityLoader->delete($entity1, false);
        $this->testEntities[] = $entity1;

        // Create an entity that is before the cutoff but not deleted
        $entity2 = $entityLoader->create($this->testDefinition->getObjType());
        $entityLoader->save($entity2);
        $this->testEntities[] = $entity2;

        // Get a cutoff
        $cutoff = new \DateTime();

        // Pause for a second to make sure the second entity is after the cutoff
        sleep(1);

        // This entity will be deleted after the cutoff so it should be left alone
        $entity3 = $entityLoader->create($this->testDefinition->getObjType());
        $entityLoader->save($entity3);
        $entityLoader->delete($entity3, false);
        $this->testEntities[] = $entity3;

        // Purge entities deleted before the cutoff date
        $purged = $this->maintainerService->purgeStaleDeletedForType($this->testDefinition, $cutoff);

        // Assert that we deleted the first entity but not the second
        $this->assertEquals(1, count($purged));
    }

    public function testDeleteOldSpamMessages()
    {
        $entityLoader = $this->account->getServiceManager()->get("EntityLoader");

        $timeEnteredAndCutoff = time();

        // Create a spam message to delete that is made a little earlier than $timeEnteredAndCutoff
        $entity1 = $entityLoader->create('email_message');
        $entity1->setValue("ts_entered", $timeEnteredAndCutoff - 1000);
        $entity1->setValue("flag_spam", true);
        $entityLoader->save($entity1);
        $this->testEntities[] = $entity1;

        // Create a second message that is not yet old enough to delete
        $entity2 = $entityLoader->create('email_message');
        $entity2->setValue("ts_entered", $timeEnteredAndCutoff + 1000);
        $entity2->setValue("flag_spam", true);
        $entityLoader->save($entity2);
        $this->testEntities[] = $entity2;

        // Get a cutoff
        $cutoff = new \DateTime();
        $cutoff->setTimestamp($timeEnteredAndCutoff);

        // Delete messages created before or on the cutoff date
        $deleted = $this->maintainerService->deleteOldSpamMessages($cutoff);

        // Assert that the message above was deleted but the second one was not
        $this->assertTrue(in_array($entity1->getId(), $deleted));
        $this->assertFalse(in_array($entity2->getId(), $deleted));
    }

    /**
     * Make sure we can clean the temp folder
     */
    public function testCleanTempFolder()
    {
        $entityLoader = $this->account->getServiceManager()->get("EntityLoader");
        $fileSystem = $this->account->getServiceManager()->get(FileSystem::class);

        // Create test folder
        $testTempFolder = $fileSystem->openFolder("/testCleanTempFolder", true);
        $this->testEntities[] = $testTempFolder;

        // Import a file imto a temp folder
        $testData = "test data";
        $file1 = $fileSystem->createFile("/testCleanTempFolder", "testTempFile.txt", true);
        $fileSystem->writeFile($file1, $testData);
        $this->testEntities[] = $file1;
        $fileId1 = $file1->getId();

        // Get a cutoff
        $cutoff = new \DateTime();

        // Create a second file with a later time than cutoff so we can make sure it is not purged
        $file2 = $fileSystem->createFile("/testCleanTempFolder", "testTempFile2.txt", true);
        $fileSystem->writeFile($file2, $testData);
        $this->testEntities[] = $file2;
        $fileId2 = $file2->getId();

        // Bump the ts_created timestamp of the second file to make it later than the cutoff
        $file2->setValue('ts_entered', ((int) $file2->getValue('ts_entered') + 10));
        $entityLoader->save($file2);

        // Run cleanTempFolder
        $deleted = $this->maintainerService->cleanTempFolder($cutoff, "/testCleanTempFolder");

        // Assure that we deleted the first file
        $this->assertTrue(in_array($fileId1, $deleted));

        // Make sure we did not prematurely delete the second file
        $this->assertFalse(in_array($fileId2, $deleted));
    }
}