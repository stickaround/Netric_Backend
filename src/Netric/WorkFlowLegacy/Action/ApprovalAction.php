<?php

/**
 * @author Sky Stebnicki, sky.stebnicki@aereus.com
 * @copyright Copyright (c) 2015 Aereus Corporation (http://www.aereus.com)
 */

namespace Netric\WorkFlowLegacy\Action;

use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
use Netric\WorkFlowLegacy\WorkFlowLegacyInstance;
use RuntimeException;

/**
 * Action to request approval on an entity
 */
class ApprovalAction extends AbstractAction implements ActionInterface
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
        $params = $this->getParams($entity);

        // TODO: This still needs to be implemented
        throw new RuntimeException("This action has not yet been implemented");
    }
}
