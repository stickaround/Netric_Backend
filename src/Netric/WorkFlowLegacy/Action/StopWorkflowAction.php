<?php

/**
 * @author Sky Stebnicki, sky.stebnicki@aereus.com
 * @copyright Copyright (c) 2015 Aereus Corporation (http://www.aereus.com)
 */

namespace Netric\WorkFlowLegacy\Action;

use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
use Netric\WorkFlowLegacy\WorkFlowLegacyInstance;

/**
 * Action to stop a child workflow
 */
class StopWorkflowAction extends AbstractAction implements ActionInterface
{
    /**
     * Execute this action
     *
     * @param WorkFlowLegacyInstance $workflowInstance The workflow instance we are executing in
     * @return bool true on success, false on failure
     */
    public function execute(WorkFlowLegacyInstance $workflowInstance)
    {
        // Get merged params
        $params = $this->getParams($workflowInstance->getEntity());

        // TODO: This is not yet implemented
        throw new \RuntimeException("Stop workflow action not yet implemented");
    }
}
