<?php

namespace NetricTest\Entity;

use PHPUnit\Framework\TestCase;
use Netric\Entity\EntityInterface;
use Netric\EntityDefinition\EntityDefinition;
use Netric\Entity\EntityMaintainerService;
use Netric\Log\LogInterface;
use Netric\Permissions\Dacl;
use Netric\EntityDefinition\EntityDefinitionLoader;
use Netric\FileSystem\FileSystem;
use Netric\Account\Account;
use Netric\Account\AccountContainer;
use Netric\EntityDefinition\DataMapper\EntityDefinitionDataMapperFactory;
use NetricTest\Bootstrap;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\EntityDefinition\ObjectTypes;

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
     * Account tests are running under
     *
     * @var Account
     */
    private $account = null;

    /**
     * Setup test objects and data
     */
    protected function setUp(): void
    {
        $this->account = Bootstrap::getAccount();

        // Create a temporary definition with a max cap of 1 entity
        $def = new EntityDefinition("utest_maint" . rand(), $this->account->getAccountId());
        $def->setTitle("Unit Test Maintenance");
        $def->capped = 1;
        $def->setSystem(false);
        $dacl = new Dacl();
        $def->setDacl($dacl);
        $dataMapper = $this->account->getServiceManager()->get(EntityDefinitionDataMapperFactory::class);
        $dataMapper->saveDef($def);
        $this->testDefinition = $def;

        // Setup a mock definition loader since we don't want to test all definitions
        $entityDefinitionLoader = $this->getMockBuilder(EntityDefinitionLoader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityDefinitionLoader->method('getAll')->willReturn([$def]);

        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $entityIndex = $this->account->getServiceManager()->get(IndexFactory::class);
        $fileSystem = $this->account->getServiceManager()->get(FileSystem::class);
        $log = $this->getMockBuilder(LogInterface::class)->getMock();
        $accountContainer = $this->createMock(AccountContainer::class);
        $accountContainer->method('loadById')->willReturn($this->account);
        $this->maintainerService = new EntityMaintainerService(
            $log,
            $entityLoader,
            $entityDefinitionLoader,
            $entityIndex,
            $fileSystem,
            $accountContainer
        );
    }

    /**
     * Teardown test objects and data
     */
    protected function tearDown(): void
    {
        // Cleanup test entities
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        foreach ($this->testEntities as $ent) {
            $entityLoader->delete($ent, $this->account->getAuthenticatedUser());
        }

        // Cleanup the entity definition
        $dataMapper = $this->account->getServiceManager()->get(EntityDefinitionDataMapperFactory::class);
        $dataMapper->deleteDef($this->testDefinition);
    }

    /**
     * Make sure that runAll actually runs all the cleanup tasks
     */
    public function testRunAll()
    {
        $allStats = $this->maintainerService->runAll($this->account->getAccountId());

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
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);

        // Create 2 entities which is one more than the cap
        $entity1 = $entityLoader->create($this->testDefinition->getObjType(), $this->account->getAccountId());
        $entityLoader->save($entity1, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $entity1;

        $entity2 = $entityLoader->create($this->testDefinition->getObjType(), $this->account->getAccountId());
        $entityLoader->save($entity2, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $entity2;

        // Run trimCappedForType
        $trimmed = $this->maintainerService->trimAllCappedTypes($this->account->getAccountId());

        // assert that the number of deleted is 1
        $this->assertEquals(1, count($trimmed[$this->testDefinition->getObjType()]));
    }

    /**
     * Make sure we can trim a specific object type
     */
    public function testTrimCappedForType()
    {
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);

        // Create 2 entities which is one more than the cap
        $entity1 = $entityLoader->create($this->testDefinition->getObjType(), $this->account->getAccountId());
        $entityLoader->save($entity1, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $entity1;

        $entity2 = $entityLoader->create($this->testDefinition->getObjType(), $this->account->getAccountId());
        $entityLoader->save($entity2, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $entity2;

        // Run trimCappedForType (getAll definitions will return only this->testDefinition)
        $trimmed = $this->maintainerService->trimCappedForType($this->testDefinition, $this->account->getAccountId());

        // assert that the number of deleted is 1
        $this->assertEquals(1, count($trimmed));
    }

    /**
     * Make sure we can delete all old deleted entities
     */
    public function testPurgeAllStaleDeleted()
    {
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);

        // Create then soft delete two entities
        $entity1 = $entityLoader->create($this->testDefinition->getObjType(), $this->account->getAccountId());
        $entityLoader->save($entity1, $this->account->getAuthenticatedUser());
        $entityLoader->archive($entity1, $this->account->getAuthenticatedUser());

        // Get a cutoff
        $cutoff = new \DateTime();

        // Pause for a second to make sure the second entity is after the cutoff
        sleep(1);

        // This entity will be deleted after the cutoff so it should be left alone
        $entity2 = $entityLoader->create($this->testDefinition->getObjType(), $this->account->getAccountId());
        $entityLoader->save($entity2, $this->account->getAuthenticatedUser());
        $entityLoader->archive($entity2, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $entity2;

        // Run trimCappedForType
        $purged = $this->maintainerService->purgeAllStaleDeleted($this->account->getAccountId(), $cutoff);

        // assert that the number of deleted is 1
        $this->assertEquals(1, count($purged[$this->testDefinition->getObjType()]));
    }

    /**
     * Make sure we can delete all old deleted entities
     */
    public function testPurgeStaleDeletedForType()
    {
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);

        // Create an entity to purge after deleted
        $entity1 = $entityLoader->create($this->testDefinition->getObjType(), $this->account->getAccountId());
        $entityLoader->save($entity1, $this->account->getAuthenticatedUser());
        $entityLoader->archive($entity1, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $entity1;

        // Create an entity that is before the cutoff but not deleted
        $entity2 = $entityLoader->create($this->testDefinition->getObjType(), $this->account->getAccountId());
        $entityLoader->save($entity2, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $entity2;

        // Get a cutoff
        $cutoff = new \DateTime();

        // Pause for a second to make sure the second entity is after the cutoff
        sleep(1);

        // This entity will be deleted after the cutoff so it should be left alone
        $entity3 = $entityLoader->create($this->testDefinition->getObjType(), $this->account->getAccountId());
        $entityLoader->save($entity3, $this->account->getAuthenticatedUser());
        $entityLoader->archive($entity3, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $entity3;

        // Purge entities deleted before the cutoff date
        $purged = $this->maintainerService->purgeStaleDeletedForType($this->testDefinition, $this->account->getAccountId(), $cutoff);

        // Assert that we deleted the first entity but not the second
        $this->assertEquals(1, count($purged));
    }

    public function testDeleteOldSpamMessages()
    {
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);

        $timeEnteredAndCutoff = time();

        // Create a spam message to delete that is made a little earlier than $timeEnteredAndCutoff
        $entity1 = $entityLoader->create(ObjectTypes::EMAIL_MESSAGE, $this->account->getAccountId());
        $entity1->setValue("owner_id", $this->account->getAuthenticatedUser()->getEntityId());
        $entity1->setValue("flag_spam", true);
        $entityLoader->save($entity1, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $entity1;

        $entity1->setValue("ts_entered", $timeEnteredAndCutoff - 1000);
        $entityLoader->save($entity1, $this->account->getAuthenticatedUser());

        // Create a second message that is not yet old enough to delete
        $entity2 = $entityLoader->create(ObjectTypes::EMAIL_MESSAGE, $this->account->getAccountId());
        $entity2->setValue("owner_id", $this->account->getAuthenticatedUser()->getEntityId());
        $entity2->setValue("flag_spam", true);
        $entityLoader->save($entity2, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $entity2;

        $entity2->setValue("ts_entered", $timeEnteredAndCutoff + 1000);
        $entityLoader->save($entity2, $this->account->getAuthenticatedUser());

        // Get a cutoff
        $cutoff = new \DateTime();
        $cutoff->setTimestamp($timeEnteredAndCutoff);

        // Delete messages created before or on the cutoff date
        $deleted = $this->maintainerService->deleteOldSpamMessages($this->account->getAccountId(), $cutoff);

        // Assert that the message above was deleted but the second one was not
        $this->assertTrue(in_array($entity1->getEntityId(), $deleted));
        $this->assertFalse(in_array($entity2->getEntityId(), $deleted));
    }

    /**
     * Make sure we can clean the temp folder
     */
    public function testCleanTempFolder()
    {
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $fileSystem = $this->account->getServiceManager()->get(FileSystem::class);

        // Create test folder
        $testTempFolder = $fileSystem->openOrCreateFolder(
            $fileSystem->getRootFolder($this->account->getAuthenticatedUser()),
            "testCleanTempFolder",
            $this->account->getAuthenticatedUser(),
            true
        );
        $this->testEntities[] = $testTempFolder;

        // Import a file imto a temp folder
        $testData = "test data";
        $file1 = $fileSystem->createTempFile("testTempFile.txt", $this->account->getAuthenticatedUser(), true);
        $fileSystem->writeFile($file1, $testData, $this->account->getSystemUser());
        $this->testEntities[] = $file1;
        $fileId1 = $file1->getEntityId();

        // Get a cutoff
        $cutoff = new \DateTime();

        // Create a second file with a later time than cutoff so we can make sure it is not purged
        $file2 = $fileSystem->createTempFile("testTempFile2.txt", $this->account->getAuthenticatedUser(), true);
        $fileSystem->writeFile($file2, $testData, $this->account->getSystemUser());
        $this->testEntities[] = $file2;
        $fileId2 = $file2->getEntityId();

        // Bump the ts_created timestamp of the second file to make it later than the cutoff
        $file2->setValue('ts_entered', ((int) $file2->getValue('ts_entered') + 10));
        $entityLoader->save($file2, $this->account->getAuthenticatedUser());

        // Run cleanTempFolder
        $deleted = $this->maintainerService->cleanTempFolder(
            $this->account->getAccountId(),
            $cutoff
        );

        // Assure that we deleted the first file
        $this->assertTrue(in_array($fileId1, $deleted));

        // Make sure we did not prematurely delete the second file
        $this->assertFalse(in_array($fileId2, $deleted));
    }
}
