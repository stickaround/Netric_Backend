<?php
namespace NetricTest\WorkFlow\Action;

use Netric\WorkFlow\Action\ActionInterface;
use Netric\WorkFlow\WorkFlowInstance;
use RuntimeException;

class ApprovalActionTest extends AbstractActionTests
{
    /**
     * All action tests must construct the action
     *
     * @return ActionInterface
     */
    protected function getAction()
    {
        return $this->actionFactory->create("approval");
    }

    /**
     * Make sure we can execute this action type and it works as designed
     */
    public function testExecute()
    {
        $this->expectException(RuntimeException::class);

        $approvalAction = $this->getAction();

        $workflowInstance = $this->getMockBuilder(WorkFlowInstance::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Sending through a mock workflow intance to make sure an exception is throw
        // since it is not implemented yet and should not be called.
        $approvalAction->execute($workflowInstance);
    }
}
