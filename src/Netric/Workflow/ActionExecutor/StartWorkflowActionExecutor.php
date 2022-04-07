<?php

declare(strict_types=1);

namespace Netric\Workflow\ActionExecutor;

use Error;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\ObjType\WorkflowActionEntity;
use Netric\Workflow\WorkflowService;

/**
 * Action to trigger a child workflow
 */
class StartWorkflowActionExecutor extends AbstractActionExecutor implements ActionExecutorInterface
{
    /**
     * Service for starting workflwos
     */
    private WorkflowService $workflowService;

    /**
     * Constructor
     *
     * @param EntityLoader $entityLoader
     * @param WorkflowActionEntity $actionEntity
     * @param string $appliactionUrl
     */
    public function __construct(
        EntityLoader $entityLoader,
        WorkflowActionEntity $actionEntity,
        string $applicationUrl,
        WorkflowService $workflowService
    ) {
        $this->workflowService = $workflowService;

        // Should always call the parent constructor for base dependencies
        parent::__construct($entityLoader, $actionEntity, $applicationUrl);
    }

    /**
     * Execute an action on an entity
     *
     * @param EntityInterface $actOnEntity The entity (any type) we are acting on
     * @param UserEntity $user The user who is initiating the action
     * @return bool true on success, false on failure
     */
    public function execute(EntityInterface $actOnEntity, UserEntity $user): bool
    {
        $workflowId = $this->getParam('wfid', $actOnEntity);

        if (!empty($workflowId)) {
            // TODO: Start the workflow for the entity
            return true;
        }

        // Assume failure
        $this->errors[] = new Error("No valid workflow id set to run");
        return false;
    }
}
