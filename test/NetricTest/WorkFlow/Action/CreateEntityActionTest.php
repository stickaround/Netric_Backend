<?php

namespace NetricTest\WorkFlow\Action;

use Netric\WorkFlow\WorkFlowInstance;
use Netric\WorkFlow\Action\ActionInterface;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityQuery;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\EntityDefinition\ObjectTypes;

class CreateEntityActionTest extends AbstractActionTests
{
    /**
     * Create some test IDs - these are arbitrary
     */
    const TEST_WORKFLOW_ID = '8cd88c04-055f-4373-bd7d-7a61dc9b3b6e';
    const TEST_TASK_ID = '8cd88c04-055f-4373-bd7d-7r61df9b3b6d';

    /**
     * All action tests must construct the action
     *
     * @return ActionInterface
     */
    protected function getAction()
    {
        return $this->actionFactory->create("create_entity");
    }

    public function testExecute()
    {
        $testLongName = 'utest-workflow-action-create-entity' . uniqid();
        $action = $this->getAction();
        $action->setParam('obj_type', ObjectTypes::TASK);
        $action->setParam('name', $testLongName);
        $action->setParam('owner_id', '<%owner_id%>'); // Copy from parent task

        // Get user
        $user = $this->account->getUser(null, UserEntity::USER_SYSTEM);

        // Create a test task that will create another task that copies the woner
        $task = $this->entityLoader->create(ObjectTypes::TASK);
        $task->setValue("name", "test");
        $task->setValue("owner_id", $user->getEntityId());
        $task->setEntityId(self::TEST_TASK_ID);

        // Create a fake WorkFlowInstance since the action does not a saved workflow or instance
        $workFlowInstance = new WorkFlowInstance(self::TEST_WORKFLOW_ID, $task);

        // Now execute the action and make sure it updated the field in $task
        $this->assertTrue($action->execute($workFlowInstance));

        // Get and cleanup
        $newEntityFound = false;
        $query = new EntityQuery(ObjectTypes::TASK);
        $query->where('name')->equals($testLongName);
        $index = $this->account->getServiceManager()->get(IndexFactory::class);
        $result = $index->executeQuery($query);
        for ($i = 0; $i < $result->getNum(); $i++) {
            $taskToDelete = $result->getEntity($i);
            // Make sure the user was copied from the parent task via <%owner_id%>
            $this->assertEquals($task->getValue("owner_id"), $taskToDelete->getValue("owner_id"));
            $this->entityLoader->delete($taskToDelete, true);
            $newEntityFound = true;
        }

        // Make sure that crazy entity was found
        $this->assertTrue($newEntityFound);
    }
}
