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
 * Action used for testing
 */
class TestAction extends AbstractAction implements ActionInterface
{
    /**
     * Example of a constructor - must always call the parent
     *
     * @param EntityLoader $entityLoader
     */
    public function __construct(EntityLoader $entityLoader, ActionFactory $actionFactory)
    {
        // TODO: Set dependencies here

        // Should always call the parent constructor for base dependencies
        parent::__construct($entityLoader, $actionFactory);
    }

    /**
     * Execute this action
     *
     * @param WorkFlowLegacyInstance $workflowInstance The workflow instance we are executing in
     * @return bool true on success, false on failure
     */
    public function execute(WorkFlowLegacyInstance $workflowInstance)
    {
        $entity = $workflowInstance->getEntity();

        // Get merged params
        $params = $this->getParams($entity);

        return true;
    }
}
