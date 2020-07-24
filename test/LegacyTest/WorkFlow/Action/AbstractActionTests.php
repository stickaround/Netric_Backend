<?php

namespace NetricTest\WorkFlow\Action;

use PHPUnit\Framework\TestCase;
use Netric\WorkFlow\Action\ActionFactory;
use Netric\WorkFlow\Action\ActionInterface;
use Netric\Entity\EntityLoader;
use Netric\Entity\ObjType\UserEntity;
use Netric\WorkFlow\DataMapper\DataMapperInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\WorkFlow\DataMapper\DataMapperFactory;
use Netric\EntityDefinition\ObjectTypes;
use Netric\WorkFlow\Action\Exception\CircularChildActionsException;

/**
 * Base tests for all actions
 */
abstract class AbstractActionTests extends TestCase
{
    /**
     * Create some test IDs
     */
    const TEST_ACTION_ID = '86e9ecbf-fe4d-4a2f-b84f-b355173992c4';
    const TEST_WORKFLOW_ID = '8cd88c04-055f-4373-bd7d-7a61dc9b3b6e';

    /**
     * Reference to account running for unit tests
     *
     * @var \Netric\Account\Account
     */
    protected $account = null;

    /**
     * Action factory for testing
     *
     * @var ActionFactory
     */
    protected $actionFactory = null;

    /**
     * Entity Loader
     *
     * @var EntityLoader
     */
    protected $entityLoader = null;

    /**
     * WorkFlow datamapper
     *
     * @var DataMapperInterface
     */
    protected $workFlowDataMapper = null;

    /**
     * Test user
     *
     * @var UserEntity
     */
    protected $testUser = null;

    /**
     * Setup any dependencies
     */
    protected function setUp(): void
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $sl = $this->account->getServiceManager();
        $this->actionFactory = new ActionFactory($sl);
        $this->entityLoader = $sl->get(EntityLoaderFactory::class);
        $this->workFlowDataMapper = $sl->get(DataMapperFactory::class);

        // Create a test user
        $this->testUser = $this->entityLoader->create(ObjectTypes::USER);
        $this->testUser->setValue("name", "wftest-" . rand());
        $this->testUser->setValue("email", "test@test.com");
        $this->entityLoader->save(($this->testUser));
        $this->account->setCurrentUser($this->testUser);
    }

    /**
     * Cleanup
     */
    protected function tearDown(): void
    {
        if ($this->testUser) {
            $this->entityLoader->delete($this->testUser, true);
        }
    }

    /**
     * All action tests must construct the action
     *
     * @return ActionInterface
     */
    abstract protected function getAction();

    /**
     * All action tests must test execution
     */
    abstract public function testExecute();

    /**
     * Make sure we can convert a workflow to and from an array
     */
    public function testFromAndToArray()
    {
        $actionData = [
            "guid" => self::TEST_ACTION_ID,
            "name" => "my action",
            "workflow_id" => self::TEST_WORKFLOW_ID,
            "parent_action_id" => 1,
            "actions" => [
                [
                    "id" => 789,
                    "type" => "test",
                    "name" => "my action",
                    "workflow_id" => self::TEST_WORKFLOW_ID,
                    "parent_action_id" => self::TEST_ACTION_ID,
                ],
            ],
        ];

        $action = $this->getAction();
        $action->fromArray($actionData);

        // Now get the array back and make sure it matches the original
        $retrievedData = $action->toArray();

        foreach ($actionData as $key => $value) {
            if (is_array($value)) {
                // Test expected nested array values
                foreach ($value as $subValueKey => $subValue) {
                    foreach ($subValue as $entryKey => $entryValue) {
                        if (is_array($entryValue)) {
                            // We can only go so deep, just check to make sure there same number of elements
                            $this->assertEquals(
                                count($entryValue),
                                count($retrievedData[$key][$subValueKey][$entryKey])
                            );
                        } else {
                            $this->assertEquals(
                                $entryValue,
                                $retrievedData[$key][$subValueKey][$entryKey]
                            );
                        }
                    }
                }
            } else {
                $this->assertEquals($value, $retrievedData[$key]);
            }
        }
    }

    public function testGetParamVariableFieldValue()
    {
        $action = $this->getAction();
        $user = $this->testUser;

        // Create an entity
        $task = $this->entityLoader->create(ObjectTypes::TASK);
        $task->setValue("name", "ut-action-test-task");
        $task->setValue("owner_id", $user->getEntityId(), $user->getName());

        // Setup reflection object to access protected function
        $refAction = new \ReflectionObject($action);
        $getParamVariableFieldValue = $refAction->getMethod("getParamVariableFieldValue");
        $getParamVariableFieldValue->setAccessible(true);

        // Now test calling the function with a merge field for task name
        $this->assertEquals(
            $task->getValue("name"),
            $getParamVariableFieldValue->invoke($action, $task, "name")
        );

        // Test calling with a merge field for user name (cross-entity reference)
        $this->assertEquals(
            $user->getValue("name"),
            $getParamVariableFieldValue->invoke($action, $task, "owner_id.name")
        );
    }

    public function testReplaceParamVariables()
    {
        $action = $this->getAction();
        $user = $this->testUser;

        // Create an entity
        $task = $this->entityLoader->create(ObjectTypes::TASK);
        $task->setValue("name", "ut-action-test-task");
        $task->setValue("owner_id", $user->getEntityId(), $user->getName());

        // Setup reflection object to access protected function
        $refAction = new \ReflectionObject($action);
        $replaceParamVariables = $refAction->getMethod("replaceParamVariables");
        $replaceParamVariables->setAccessible(true);

        // Make sure we can merge multiple variables into a single string
        $this->assertEquals(
            $task->getValue("name") . " - " . $user->getValue("name"),
            $replaceParamVariables->invoke($action, $task, "<%name%> - <%owner_id.name%>")
        );
    }

    public function testSetParam()
    {
        $action = $this->getAction();

        // Set params for action
        $action->setParam("subject", "test");

        // Make sure it was set
        $this->assertEquals("test", $action->getParam("subject"));
    }

    public function testGetParam()
    {
        $action = $this->getAction();

        // Set params for action
        $action->setParam("subject", "<%name%>");

        // Create an entity
        $task = $this->entityLoader->create(ObjectTypes::TASK);
        $task->setValue("name", "ut-action-test-task");

        // Make sure we can get the raw unmerged value
        $this->assertEquals("<%name%>", $action->getParam("subject"));

        // Now test merged
        $this->assertEquals($task->getValue("name"), $action->getParam("subject", $task));
    }

    public function testGetParams()
    {
        $action = $this->getAction();
        $user = $this->testUser;

        // Set params for action
        $action->setParam("subject", "Work on <%name%>");
        $action->setParam("username", "<%owner_id.name%>");

        // Create an entity
        $task = $this->entityLoader->create(ObjectTypes::TASK);
        $task->setValue("name", "ut-action-test-task");
        $task->setValue("owner_id", $user->getEntityId(), $user->getName());

        // Setup reflection object to access protected function
        $refAction = new \ReflectionObject($action);
        $getParams = $refAction->getMethod("getParams");
        $getParams->setAccessible(true);

        // Check that params are processed
        $params = $getParams->invoke($action, $task);
        $this->assertEquals(
            "Work on " . $task->getValue("name"),
            $params['subject']
        );
        $this->assertEquals(
            $user->getValue("name"),
            $params['username']
        );
    }

    public function testGetParamsObjType()
    {
        $action = $this->getAction();

        // Set params for action
        $action->setParam("subject", "Work on <%obj_type%>");

        // Create an entity
        $task = $this->entityLoader->create(ObjectTypes::TASK);
        $task->setValue("name", "ut-action-test-task");

        // Setup reflection object to access protected function
        $refAction = new \ReflectionObject($action);
        $getParams = $refAction->getMethod("getParams");
        $getParams->setAccessible(true);

        // Check that params are processed
        $params = $getParams->invoke($action, $task);
        $this->assertEquals(
            "Work on " . $task->getDefinition()->getObjType(),
            $params['subject']
        );
    }

    /**
     * Legacy scripts used to use <%oid%> rather than just <%id%> so make sure it still works
     */
    public function testGetParamsOID()
    {
        $action = $this->getAction();

        // Set params for action
        $action->setParam("subject", "Work on <%oid%>");

        // Create an entity
        $task = $this->entityLoader->create(ObjectTypes::TASK);
        $task->setValue('guid', self::TEST_ACTION_ID);
        $task->setValue("name", "ut-action-test-task");

        // Setup reflection object to access protected function
        $refAction = new \ReflectionObject($action);
        $getParams = $refAction->getMethod("getParams");
        $getParams->setAccessible(true);

        // Check that params are processed
        $params = $getParams->invoke($action, $task);
        $this->assertEquals(
            "Work on " . self::TEST_ACTION_ID,
            $params['subject']
        );
    }

    /**
     * We have some legacy features to support where a user may have
     * entered a object type = user field in an email param called 'to', 'cc', or 'bcc'
     *
     * @group legacy
     */
    public function testGetParams_Legacy()
    {
        $action = $this->getAction();

        // We set email to test@test.com to test user in $this->setUp
        $user = $this->testUser;

        // Set params for action
        $action->setParam("to", "<%owner_id%>,<%creator_id%>,<%owner_id.email%>");

        // Create an entity with a user to send to
        $task = $this->entityLoader->create(ObjectTypes::TASK);
        $task->setValue("owner_id", $user->getEntityId(), $user->getName());
        $task->setValue("creator_id", $user->getEntityId(), $user->getName());

        // Setup reflection object to access protected function
        $refAction = new \ReflectionObject($action);
        $getParams = $refAction->getMethod("getParams");
        $getParams->setAccessible(true);

        // Now make sure that 'to' was sent to the user's email and not id
        $params = $getParams->invoke($action, $task);
        $this->assertEquals(
            $user->getValue("email") . "," . $user->getValue("email") . "," . $user->getValue("email"),
            $params['to']
        );
    }

    public function testRemoveAction()
    {
        $action = $this->getAction();

        // Create a test child action
        $childAction = $this->actionFactory->create("test");
        $childAction->setId(100);
        $action->addAction($childAction);

        // Test removing the action when it's the same object
        $this->assertTrue($action->removeAction($childAction));
        $this->assertEquals(0, count($action->getActions()));
        $this->assertEquals(1, count($action->getRemovedActions()));

        // Add again which should clear it from the 'to be removed' queue
        $action->addAction($childAction);
        $this->assertEquals(1, count($action->getActions()));
        $this->assertEquals(0, count($action->getRemovedActions()));

        // Now try removing with a new object that has the same id
        $childActionClone = $this->actionFactory->create("test");
        $childActionClone->setId(100);
        $this->assertTrue($action->removeAction($childActionClone));
        $this->assertEquals(0, count($action->getActions()));
        $this->assertEquals(1, count($action->getRemovedActions()));
    }

    public function testGetRemovedActions()
    {
        $action = $this->getAction();

        // Create a test action
        $childAction = $this->actionFactory->create("test");
        $childAction->setId(100);
        $action->addAction($childAction);

        // Now delete it
        $action->removeAction($childAction);
        $removedActions = $action->getRemovedActions();
        $this->assertEquals($childAction->getWorkFlowActionId(), $removedActions[0]->getWorkFlowActionId());
    }

    public function testAddAction()
    {
        $action = $this->getAction();

        // Create a test action
        $childAction = $this->actionFactory->create("test");
        $childAction->setId(100);
        $action->addAction($childAction);
        $this->assertEquals(1, count($action->getActions()));

        // Try adding the same action again which should result only in one
        $action->addAction($childAction);
        $this->assertEquals(1, count($action->getActions()));

        // Remove it, then add again and make sure removed is empty
        $action->removeAction($childAction);
        $action->addAction($childAction);

        $this->assertEquals(1, count($action->getActions()));
        $this->assertEquals(0, count($action->getRemovedActions()));
    }

    public function testGetActions()
    {
        $action = $this->getAction();

        // Create a test action
        $childAction = $this->actionFactory->create("test");
        $childAction->setId(100);
        $action->addAction($childAction);

        // Make sure the action is in the queue of actions
        $actions = $action->getActions();
        $this->assertEquals($childAction->getId(), $actions[0]->getId());
    }

    /**
     * Make sure an action cannot add itself
     */
    public function testAddAction_NotSelf()
    {
        $this->expectException(CircularChildActionsException::class);
        $action = $this->getAction();
        $action->addAction($action);
    }

    /**
     * Make sure a descendant never adds a parent
     */
    public function testAddAction_NotSCircular()
    {
        $this->expectException(CircularChildActionsException::class);
        $action = $this->getAction();

        // Create a test action
        $childAction = $this->actionFactory->create("test");
        $childAction->setId(100);
        $action->addAction($childAction);

        // Now try to add the parent action to the child
        $childAction->addAction($action);
    }
}
