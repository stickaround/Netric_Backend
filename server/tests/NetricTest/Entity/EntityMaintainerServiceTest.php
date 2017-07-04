<?php
namespace NetricTest\Entity;

use PHPUnit\Framework\TestCase;
use Netric\Entity\EntityInterface;
use Netric\EntityDefinition\EntityDefinition;
use Netric\Entity\EntityMaintainerService;
use Netric\Log\LogInterface;
use Netric\Permissions\Dacl;
use Netric\EntityDefinitionLoader;

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
        $log = $this->getMockBuilder(LogInterface::class)->getMock();
        $this->maintainerService = new EntityMaintainerService(
            $log,
            $entityLoader,
            $entityDefinitionLoader,
            $entityIndex
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

        // Create then soft delete two entities
        $entity1 = $entityLoader->create($this->testDefinition->getObjType());
        $entityLoader->save($entity1);
        $entityLoader->delete($entity1, false);
        $this->testEntities[] = $entity1;

        // Get a cutoff
        $cutoff = new \DateTime();

        // Pause for a second to make sure the second entity is after the cutoff
        sleep(1);

        // This entity will be deleted after the cutoff so it should be left alone
        $entity2 = $entityLoader->create($this->testDefinition->getObjType());
        $entityLoader->save($entity2);
        $entityLoader->delete($entity2, false);
        $this->testEntities[] = $entity2;

        // Purge entities deleted before the cutoff date
        $purged = $this->maintainerService->purgeStaleDeletedForType($this->testDefinition, $cutoff);

        // Assert that we deleted the first entity but not the second
        $this->assertEquals(1, count($purged));
    }
}