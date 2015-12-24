<?php
namespace NetricTest\WorkFlow\Action;

use Netric\WorkFlow\Action\ActionInterface;

class StartWorkflowActionTest extends AbstractActionTests
{
    /**
     * All action tests must construct the action
     *
     * @return ActionInterface
     */
    protected function getAction()
    {
        return $this->actionFactory->create("start_workflow");
    }
}
