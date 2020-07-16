<?php

namespace NetricTest\WorkFlow\Action;

use Netric\WorkFlow\Action\ActionInterface;
use Netric\WorkFlow\WorkFlowInstance;
use Netric\EntityDefinition\ObjectTypes;

class StopWorkflowActionTest extends AbstractActionTests
{
    const TEST_TASK_ID = '811bc76e-b99c-4f3d-be0b-596e42ff7f9d';

    /**
     * All action tests must construct the action
     *
     * @return ActionInterface
     */
    protected function getAction()
    {
        return $this->actionFactory->create("stop_workflow");
    }

    /**
     * Make sure we can execute this action type and it works as designed
     */
    public function testExecute()
    {
        $this->expectException(\RuntimeException::class);
        $action = $this->getAction();

        // Create a task that will email the owner when completed
        $task = $this->entityLoader->create(ObjectTypes::TASK);
        $task->setValue("name", "test");
        $task->setValue('guid', self::TEST_TASK_ID);

        // Create a fake WorkFlowInstance since the action does not a saved workflow or instance
        $workFlowInstance = new WorkFlowInstance(self::TEST_TASK_ID, $task);

        // This action is not implemented yet so we throw an exception
        $action->execute($workFlowInstance);
    }
}
