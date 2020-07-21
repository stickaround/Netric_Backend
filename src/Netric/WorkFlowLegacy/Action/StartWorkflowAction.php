<?php

/**
 * @author Sky Stebnicki, sky.stebnicki@aereus.com
 * @copyright Copyright (c) 2015 Aereus Corporation (http://www.aereus.com)
 */

namespace Netric\WorkFlowLegacy\Action;

use Netric\Entity\EntityLoader;
use Netric\Error\Error;
use Netric\WorkFlowLegacy\WorkFlowLegacyInstance;
use Netric\WorkFlowLegacy\WorkFlowLegacyManager;

/**
 * Action to trigger a child workflow
 */
class StartWorkflowAction extends AbstractAction implements ActionInterface
{
    /**
     * Manager for starting WorkFlowLegacys
     *
     * @var WorkFlowLegacyManager|null
     */
    private $workFlowManager = null;

    /**
     * This must be called by all derived classes, or $entityLoader should be set in their constructor
     *
     * @param EntityLoader $entityLoader
     * @param ActionFactory $actionFactory For constructing child actions
     * @param WorkFlowLegacyManager $workFlowManager For starting a child workflow
     */
    public function __construct(
        EntityLoader $entityLoader,
        ActionFactory $actionFactory,
        WorkFlowLegacyManager $workFlowManager
    ) {
        $this->workFlowManager = $workFlowManager;
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

        if (isset($params['wfid'])) {
            $this->workFlowManager->startWorkflowById($entity, $params['wfid']);
            return true;
        }

        // Assume failure
        $this->errors[] = new Error("No valid workflow id set to run");
        return false;
    }
}
