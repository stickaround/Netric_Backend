<?php
/**
 * Test the WorkFlow class
 */
namespace NetricTest\WorkFlow;

use Netric\WorkFlow\Action\ActionFactory;
use Netric\WorkFlow\WorkFlowFactory;
use Netric\EntityQuery\Where;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
use Netric\WorkFlow\WorkFlowManager;
use Netric\WorkFlow\DataMapper\DataMapperInterface;
use Netric\WorkFlow\WorkFlowInstance;
use PHPUnit\Framework\TestCase;
use Netric\Entity\EntityLoaderFactory;
use Netric\WorkFlow\WorkFlowManagerFactory;
use Netric\WorkFlow\DataMapper\DataMapperFactory;
use Netric\EntityDefinition\ObjectTypes;
use NetricTest\Bootstrap;

/*
 * @group integration
 */
class WorkFlowManagerTest extends TestCase
{
    /**
     * Test entities to delete
     *
     * @var EntityInterface
     */
    private $testEntities = array();


    /**
     * Reference to account running for unit tests
     *
     * @var \Netric\Account\Account
     */
    private $account = null;

    /**
     * Action factory for testing
     *
     * @var ActionFactory
     */
    private $actionFactory = null;

    /**
     * Entity loader
     *
     * @var EntityLoader
     */
    private $entityLoader = null;

    /**
     * WorkFlowManager to test
     *
     * @var WorkFlowManager
     */
    private $workFlowManager = null;

    /**
     * ServiceLocator for injecting dependencies
     * 
     * @var AccountServiceManagerInterface
     */
    private $sl = null;

    /**
     * Work flow datamapper for saving worklfows
     *
     * @var DataMapperInterface
     */
    private $workFlowDataMapper = null;

    protected function setUp(): void
{
        $this->account = Bootstrap::getAccount();
        $this->sl = $this->account->getServiceManager();
        $this->actionFactory = new ActionFactory($this->sl);
        $this->entityLoader = $this->sl->get(EntityLoaderFactory::class);
        $this->workFlowManager = $this->sl->get(WorkFlowManagerFactory::class);
        $this->workFlowDataMapper = $this->sl->get(DataMapperFactory::class);
    }

    protected function tearDown(): void
{
        foreach ($this->testEntities as $entity) {
            $this->entityLoader->delete($entity, true);
        }
    }

    public function testStartWorkFlows()
    {
        /*
         * Create a test entity to run on before saving
         * the workflow so we do not trigger it in the entity datamapper
         */
        $task = $this->entityLoader->create(ObjectTypes::TASK);
        $task->setValue("name", "test");
        $task->setValue("done", false); // should cause it to be ignored by the WorkFlow
        $this->entityLoader->save($task);
        $this->testEntities[] = $task;

        // Create a new workflow with conditions
        $workFlow = $this->sl->get(WorkFlowFactory::class);
        $workFlow->setObjType(ObjectTypes::TASK);
        $workFlow->setOnlyOnConditionsUnmet(true);
        $workFlow->setOnUpdate(true);
        $condition = new Where("done");
        $condition->equals(true);
        $workFlow->addCondition($condition);

        // Setup a test action to change the name to 'automatically changed'
        $actionUpdateField = $this->actionFactory->create("update_field");
        $actionUpdateField->setParam('update_field', 'name');
        $actionUpdateField->setParam('update_value', 'automatically changed');
        $workFlow->addAction($actionUpdateField);

        // Save the workflow
        $this->workFlowDataMapper->save($workFlow);

        // First pass should not run anything
        $this->workFlowManager->startWorkFlows($task, "update");

        // Make sure the entity was not changed
        $this->assertEquals('test', $task->getValue("name"));

        // Update the entity to match WorkFlow conditions, then run WorkFlows
        $task->setValue("done", true);
        $this->entityLoader->save($task);

        /*
         * This is a little hackey, but we have to mark the 'done' field as dirty
         * because the conditions check against the saved values, but also looks to
         * see if the field value has changed due to setOnlyOnConditionsUnmet.
         * In the entity datamapper when the workflows are run, startWorkFlows is
         * called before the reset dirty function is called which avoids this problem.
         */
        $task->setValue("done", false);
        $task->setValue("done", true);
        $this->workFlowManager->startWorkFlows($task, "update");

        // Make sure the entity was changed
        $this->assertEquals('automatically changed', $task->getValue("name"));

        /*
         * Do not change done, but change the name and run again which should not
         * cause the workflow to run since
         */
        $task->setValue("name", "test");
        $this->entityLoader->save($task);
        $this->workFlowManager->startWorkFlows($task, "update");

        // Make sure the entity was not changed since 'done' did not change
        $this->assertEquals('test', $task->getValue("name"));

        // Cleanup
        $this->workFlowDataMapper->delete($workFlow);
    }

    public function testRunPeriodicWorkFlows()
    {
        /*
         * Create a test entity to run on before saving
         * the workflow so we do not trigger it in the entity datamapper
         */
        $task = $this->entityLoader->create(ObjectTypes::TASK);
        $task->setValue("name", "test");
        $task->setValue("done", true);
        $this->entityLoader->save($task);
        $this->testEntities[] = $task;

        // Create a new workflow with conditions
        $workFlow = $this->sl->get(WorkFlowFactory::class);
        $workFlow->setObjType(ObjectTypes::TASK);
        $workFlow->setOnlyOnConditionsUnmet(true);
        $workFlow->setOnDaily(true);
        $condition = new Where("done");
        $condition->equals(true);
        $workFlow->addCondition($condition);
        $condition2 = new Where("id");
        $condition2->equals($task->getEntityId()); // Do not update other done tasks
        $workFlow->addCondition($condition2);

        // Setup a test action to change the name to 'automatically changed'
        $actionUpdateField = $this->actionFactory->create("update_field");
        $actionUpdateField->setParam('update_field', 'name');
        $actionUpdateField->setParam('update_value', 'automatically changed');
        $workFlow->addAction($actionUpdateField);

        // Save the workflow
        $this->workFlowDataMapper->save($workFlow);

        // Run a pass
        $this->workFlowManager->runPeriodicWorkFlows();

        // Make sure the entity was changed
        $openedTask = $this->entityLoader->getByGuid($task->getEntityId());
        $this->assertEquals('automatically changed', $openedTask->getValue("name"));

        /*
         * Do not change done, but change the name and run again which should not
         * cause the workflow to run since it just ran for this period
         */
        $openedTask->setValue("name", "test");
        $this->entityLoader->save($openedTask);
        $this->workFlowManager->runPeriodicWorkFlows();

        // Make sure that the entity was not changed since 'daily' was already run before
        $openedTask = $this->entityLoader->getByGuid($task->getEntityId());
        $this->assertEquals('test', $openedTask->getValue("name"));

        // Cleanup
        $this->workFlowDataMapper->delete($workFlow);
    }

    public function testRunScheduledActions()
    {
        /*
         * Create a test entity to run on before saving
         * the workflow so we do not trigger it in the entity datamapper
         */
        $task = $this->entityLoader->create(ObjectTypes::TASK);
        $task->setValue("name", "test");
        $this->entityLoader->save($task);
        $this->testEntities[] = $task;

        // Create a new workflow with conditions
        $workFlow = $this->sl->get(WorkFlowFactory::class);
        $workFlow->setObjType(ObjectTypes::TASK);
        $workFlow->setOnUpdate(true);

        // Setup a test action to change the name to 'automatically changed'
        $actionUpdateField = $this->actionFactory->create("update_field");
        $actionUpdateField->setParam('update_field', 'name');
        $actionUpdateField->setParam('update_value', 'automatically changed');
        $workFlow->addAction($actionUpdateField);

        // Save the workflow
        $this->workFlowDataMapper->save($workFlow);

        // Start a new test/fake instance
        $workFlowInstance = new WorkFlowInstance($workFlow->getId(), $task);
        $this->workFlowDataMapper->saveWorkFlowInstance($workFlowInstance);

        /*
         * Schedule the action for now so it triggers immediately when we
         * look for previously scheduled actions.
         * In normal scenarios this would be done by creating a WaitConditionAction
         * which schedules itself and then executes children once it is called.
         * This essentially does the same thing but just schedules the child task
         * specifically for the sake of testing.
         */
        $result = $this->workFlowDataMapper->scheduleAction(
            $workFlowInstance->getId(),
            $actionUpdateField->getId(),
            new \DateTime(date("Y-m-d"))
        );

        $this->assertTrue($result);

        // Run scheduled actions which should execute the above
        $this->workFlowManager->runScheduledActions();

        // Get the entity again and make sure it was changed by the above action
        $openedTask = $this->entityLoader->getByGuid($task->getEntityId());
        $this->assertEquals('automatically changed', $openedTask->getValue("name"));

        // Make sure the scheduled action is deleted
        $this->assertNull(
            $this->workFlowDataMapper->getScheduledActionTime(
                $workFlowInstance->getId(),
                $actionUpdateField->getId()
            )
        );

        // Cleanup
        $this->workFlowDataMapper->delete($workFlow);
    }
}
