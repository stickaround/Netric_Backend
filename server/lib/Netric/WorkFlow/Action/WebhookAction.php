<?php
/**
 * @author Sky Stebnicki, sky.stebnicki@aereus.com
 * @copyright Copyright (c) 2015 Aereus Corporation (http://www.aereus.com)
 */
namespace Netric\WorkFlow\Action;

use Netric\Entity\EntityInterface;
use Netric\EntityLoader;
use Netric\WorkFlow\WorkFlowInstance;

/**
 * Action to call an external page - very useful for API integration
 */
class WebhookAction extends AbstractAction implements ActionInterface
{
    /**
     * Execute this action
     *
     * @param WorkFlowInstance $workflowInstance The workflow instance we are executing in
     * @return bool true on success, false on failure
     */
    public function execute(WorkFlowInstance $workflowInstance)
    {
        // Get merged params
        $params = $this->getParams($entity);

        // TODO: Finish action
    }
}