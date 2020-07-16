<?php

namespace NetricTest\WorkFlow\Action;

use Netric\WorkFlow\Action\ActionInterface;
use Netric\WorkFlow\WorkFlowInstance;
use Netric\EntityDefinition\ObjectTypes;

class TestActionTest extends AbstractActionTests
{
    /**
     * Create some test IDs - these are arbitrary
     */
    const TEST_TASK_ID = '8cd88c04-055f-4373-bd7d-7a61dc9b3b6e';

    /**
     * All action tests must construct the action
     *
     * @return ActionInterface
     */
    protected function getAction()
    {
        return $this->actionFactory->create("test");
    }

    /**
     * Make sure we can execute this action type and it works as designed
     */
    public function testExecute()
    {
        $action = $this->getAction();

        // Create a task that will email the owner when completed
        $task = $this->entityLoader->create(ObjectTypes::TASK);
        $task->setValue('name', 'test');
        $task->setValue('guid', self::TEST_TASK_ID);

        // Create a fake WorkFlowInstance since the action does not a saved workflow or instance
        $workFlowInstance = new WorkFlowInstance(self::TEST_WORKFLOW_ID, $task);

        $this->assertTrue($action->execute($workFlowInstance));
    }
}
