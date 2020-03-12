<?php
/**
 * Make sure the bin/scripts/update/once/004/001/031.php script works
 */
namespace BinTest\Update\Once;

use Netric\Console\BinScript;
use PHPUnit\Framework\TestCase;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Entity\ObjType\TaskEntity;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityQuery;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Netric\Entity\DataMapper\DataMapperFactory as EntityDataMapperFactory;
use Netric\Log\LogFactory;
use Netric\Db\Relational\RelationalDbFactory;


class Update004001031Test extends TestCase
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
     * Entities to cleanup
     *
     * @var array
     */
    private $testEntities = array();

    /**
     * Setup each test
     */
    protected function setUp(): void
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $this->scriptPath = __DIR__ . "/../../../../bin/scripts/update/once/004/001/031.php";
        $this->entityDataMapper = $this->account->getServiceManager()->get(EntityDataMapperFactory::class);
    }

    /**
     * Cleanup after a test runs
     */
    protected function tearDown(): void
    {
        // Cleanup any test entities
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        foreach ($this->testEntities as $entity) {
            $entityLoader->delete($entity, true);
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
        $entityDataMapper = $serviceManager->get(EntityDataMapperFactory::class);
        $entityLoader = $serviceManager->get(EntityLoaderFactory::class);
        $groupingLoader = $this->account->getServiceManager()->get(GroupingLoaderFactory::class);
        $rdb = $this->account->getServiceManager()->get(RelationalDbFactory::class);
        $log = $this->account->getServiceManager()->get(LogFactory::class);

        // Make sure that the project story table still exists
        $projectStoryTableName = "objects_project_story";
        if (!$rdb->tableExists($projectStoryTableName)) {
            $log->warning("Update004001031Test:: Unit tests is skiped because project story table is not available anymore");
            return;
        }

        $statusStoryGroupings = $groupingLoader->get("project_story/status_id");
        $priorityStoryGroupings = $groupingLoader->get("project_story/priority_id");
        $typeStoryGroupings = $groupingLoader->get("project_story/type_id");

        // Create a new project so we can assign this project to the story
        $projectEntity = $entityLoader->create(ObjectTypes::PROJECT); 
        $projectEntity->setValue("name", "Project to be used.");
        $testEntities[] = $entityDataMapper->save($projectEntity);

        // Create a project story entity
        $storyEntity = $entityLoader->create("project_story");
        $storyEntity->setValue("name", "Project story to move.");
        $storyEntity->setValue("date_start", "2020-02-02");
        $storyEntity->setValue("project_id", $projectEntity->getId(), $projectEntity->getValue("name"));

        $statusStoryGroup = $statusStoryGroupings->getByName("Completed");
        $priorityStoryGroup = $priorityStoryGroupings->getByName("High");
        $typeStoryGroup = $typeStoryGroupings->getByName("Defect");

        // Set the story's group id and name for status_id, priority_id, and type_id
        $storyEntity->setValue("status_id", $statusStoryGroup->id, $statusStoryGroup->name);
        $storyEntity->setValue("priority_id", $priorityStoryGroup->id, $priorityStoryGroup->name);
        $storyEntity->setValue("type_id", $typeStoryGroup->id, $typeStoryGroup->name);
        $testEntities[] = $entityDataMapper->save($storyEntity);

        // Create new comment so we can set this comment to the story
        $commentEntity = $entityLoader->create(ObjectTypes::COMMENT); 
        $commentEntity->setValue("comment", "Comment in the story.");
        $commentEntity->setValue("obj_reference", "project_story:{$storyEntity->getId()}", $storyEntity->getName());
        $testEntities[] = $entityDataMapper->save($commentEntity);
        
        // Execute the script
        $binScript = new BinScript($this->account->getApplication(), $this->account);
        $this->assertTrue($binScript->run($this->scriptPath));

        $statusTaskGroupings = $groupingLoader->get(ObjectTypes::TASK . "/status_id");
        $priorityTaskGroupings = $groupingLoader->get(ObjectTypes::TASK . "/priority_id");
        $typeTaskGroupings = $groupingLoader->get(ObjectTypes::TASK . "/type_id");

        $statusTaskGroup = $statusTaskGroupings->getByName(TaskEntity::STATUS_COMPLETED);
        $priorityTaskGroup = $priorityTaskGroupings->getByName(TaskEntity::PRIORITY_HIGH);
        $typeTaskGroup = $typeTaskGroupings->getByName(TaskEntity::TYPE_DEFECT);

        $movedEntity = $entityLoader->getByGuid($storyEntity->getGuid());
        $this->testEntities[] = $movedEntity;

        // Perform the tests
        $this->assertEquals($movedEntity->getObjType(), ObjectTypes::TASK);
        $this->assertEquals($movedEntity->getValue("status_id"), $statusTaskGroup->id);
        $this->assertEquals($movedEntity->getValue("priority_id"), $priorityTaskGroup->id);
        $this->assertEquals($movedEntity->getValue("type_id"), $typeTaskGroup->id);
        $this->assertEquals($movedEntity->getValue("project"), $projectEntity->getId());
        $this->assertEquals($movedEntity->getValueName("project"), $projectEntity->getValue("name"));
        $this->assertEquals($movedEntity->getValue("done"), true);
        $this->assertEquals(date("Y-m-d", $movedEntity->getValue("start_date")), "2020-02-02");

        // Do a test that will make sure obj_reference in comment entity where also updated
        $commentEnt = $entityLoader->getByGuid($commentEntity->getGuid());
        $this->assertEquals($commentEnt->getValue("obj_reference"), ObjectTypes::TASK . ":{$movedEntity->getId()}");

        // Test that project entity were already deleted after it was moved
        $result = $rdb->query("SELECT * FROM $projectStoryTableName WHERE id = :id", ["id" => $storyEntity->getId()]);
        $this->assertEquals($result->rowCount(), 0);
    }
}