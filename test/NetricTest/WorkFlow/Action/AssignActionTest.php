<?php

namespace NetricTest\WorkFlow\Action;

use Netric\EntityGroupings\Group;
use Netric\WorkFlow\Action\ActionInterface;
use Netric\WorkFlow\WorkFlowInstance;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityGroupings\GroupingLoaderFactory;

class AssignActionTest extends AbstractActionTests
{
    /**
     * Create some test IDs
     */
    const TEST_ACTION_ID = '86e9ecbf-fe4d-4a2f-b84f-b355173992c4';
    const TEST_WORKFLOW_ID = '8cd88c04-055f-4373-bd7d-7a61dc9b3b6e';

    /**
     * Test entities to delete
     *
     * @var EntityInterface[]
     */
    private $testEntities = [];

    /**
     * Test WorkFlows to cleanup
     *
     * @var WorkFlow[]
     */
    private $testWorkFlows = [];

    /**
     * Cleanup entities
     */
    protected function tearDown(): void
    {
        foreach ($this->testEntities as $entity) {
            $this->entityLoader->delete($entity, true);
        }

        foreach ($this->testWorkFlows as $workFlow) {
            $this->workFlowDataMapper->delete($workFlow);
        }

        parent::tearDown();
    }

    /**
     * All action tests must construct the action
     *
     * @return ActionInterface
     */
    protected function getAction()
    {
        return $this->actionFactory->create("assign");
    }

    /**
     * Test execution with a manual users list
     */
    public function testExecute()
    {
        // Create three users for assignment
        $user1 = $this->entityLoader->create(ObjectTypes::USER);
        $user1->setValue("name", "testuser-" . rand());
        $this->entityLoader->save($user1);
        $userGuid1 = $user1->getEntityId();
        $this->testEntities[] = $user1;

        $user2 = $this->entityLoader->create(ObjectTypes::USER);
        $user2->setValue("name", "testuser-" . rand());
        $this->entityLoader->save($user2);
        $userGuid2 = $user2->getEntityId();
        $this->testEntities[] = $user2;

        $user3 = $this->entityLoader->create(ObjectTypes::USER);
        $user3->setValue("name", "testuser-" . rand());
        $this->entityLoader->save($user3);
        $userGuid3 = $user3->getEntityId();
        $this->testEntities[] = $user3;

        $usersArray = [$userGuid1, $userGuid2, $userGuid3];

        // Create new action and set values for the userlist
        $action = $this->getAction();
        $action->setParam('field', 'owner_id');
        $action->setParam('users', implode(',', $usersArray));

        // Create a test task
        $task = $this->entityLoader->create(ObjectTypes::TASK);
        $task->setValue("name", "test");
        $this->entityLoader->save($task);
        $this->testEntities[] = $task;

        // Create a fake WorkFlowInstance since the action does not a saved workflow or instance
        $workFlowInstance = new WorkFlowInstance(self::TEST_WORKFLOW_ID, $task);

        // Now execute the action and make sure it updated the field in $task
        $this->assertTrue($action->execute($workFlowInstance));

        // Make sure the user was assigned to one of the users
        $this->assertTrue(in_array($task->getValue("owner_id"), $usersArray));

        // Execute repeatedly and check the probability distribution
        $hits = [$userGuid1 => 0, $userGuid2 => 0, $userGuid3 => 0];
        for ($i = 0; $i < 100; $i++) {
            $action->execute($workFlowInstance);
            $hits[$task->getValue('owner_id')]++;
        }

        // Make sure probabilities are in acceptable ranges ~20% to each
        $this->assertGreaterThan(20, $hits[$userGuid1]);
        $this->assertGreaterThan(20, $hits[$userGuid2]);
        $this->assertGreaterThan(20, $hits[$userGuid3]);
    }
}
