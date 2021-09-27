<?php

declare(strict_types=1);

namespace Netric\Workflow;

use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\ObjType\WorkflowEntity;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\Workflow\ActionExecutorFactory;
use Netric\Entity\EntityInterface;
use Netric\Entity\ObjType\WorkflowActionEntity;
use Netric\Workflow\DataMapper\WorkflowDataMapperInterface;
use Netric\Log\LogInterface;

/**
 * Workflow service responsible for running/stopping workflows on entities with various events
 */
class WorkflowService
{
    /**
     * WorkFlowLegacy DataMapper
     */
    private WorkflowDataMapperInterface $workFlowDataMapper;

    /**
     * Logger interface
     */
    private LogInterface $log;

    /**
     * Create action executors
     *
     * @var ActionExecutorFactory
     */
    private ActionExecutorFactory $actionFactory;

    /**
     * Set dependencies and construct the manager
     *
     * @param WorkflowDataMapperInterface $workFlowDataMapper
     * @param ActionExecutorFactory $actionFactory
     * @param IndexInterface $index The query index interface
     * @param LogInterface $log Netric log
     */
    public function __construct(
        WorkflowDataMapperInterface $workFlowDataMapper,
        ActionExecutorFactory $actionFactory,
        LogInterface $log
    ) {
        $this->workFlowDataMapper = $workFlowDataMapper;
        $this->actionFactory = $actionFactory;
        $this->log = $log;
    }

    /**
     * Start workflows for an entity based on an action
     *
     * @param EntityInterface $entity The entity being acted on
     * @param string $eventName One of Workflow::EVENT_
     * @param UserEntity $user The user that triggered the event
     */
    public function runWorkflowsOnEvent(EntityInterface $entity, string $eventName, UserEntity $user): void
    {
        // Get array of active entities that are listening for changes for the given entity type
        $entityType = $entity->getDefinition()->getObjType();
        $activeWorkflows = $this->workFlowDataMapper->getActiveWorkflowsForEvent(
            $entityType,
            $user->getAccountId(),
            $eventName
        );

        foreach ($activeWorkflows as $workflow) {
            // Make sure we don't re-run a job if the workflow is an f_singleton
            if (
                $workflow->getValue('f_singleton') &&
                $this->workFlowDataMapper->getInstancesForEntity($workflow, $entity) != []
            ) {
                // Skip the workflow because it has already run
                continue;
            }

            $this->log->info(
                "WorkflowService->runWorkflowsOnEvent: running workflow " .
                    $workflow->getEntityId() .
                    " against " .
                    $entity->getEntityId() .
                    " on event " .
                    $eventName
            );

            // Start an instance
            $this->startInstanceAndRunActions($workflow, $entity, $user);
        }
    }

    /**
     * Start a workflow instance given a WorkFlowLegacy and an Entity
     *
     * @param WorkflowEntity $workflow The WorkFlowLegacy we would like to run
     * @param EntityInterface $entity The entity we are running on
     * @param UserEntity $user
     */
    private function startInstanceAndRunActions(WorkflowEntity $workflow, EntityInterface $entity, UserEntity $user)
    {
        // Create a new instance for this workflow and entity
        $this->workFlowDataMapper->createWorkflowInstance(
            $workflow,
            $entity,
            $user
        );

        // Now execute first level of actions in the workflow
        $actions = $this->workFlowDataMapper->getActions($user->getAccountId(), $workflow->getEntityId());
        $this->log->info(
            "WorkflowService->startInstanceAndRunActions: running" .
                count($actions) .
                " against " .
                $entity->getEntityId()
        );
        foreach ($actions as $action) {
            $this->executeAction($action, $entity, $user);
        }
    }

    /**
     * Execute an action for a workflow instance
     *
     * @param EntityInterface $workflowInstance The instance we are running
     * @param WorkflowActionEntity $action Entity with action state
     * @param EntityInterface $actOnEntity The entity we are acting on
     * @param UserEntity $user
     */
    private function executeAction(
        WorkflowActionEntity $actionEntity,
        EntityInterface $actOnEntity,
        UserEntity $user
    ) {
        // Get the action executor - we split this because the entity state for action is pretty
        // generic but the execution is highly dynamic and specific.
        $actionExecutor = $this->actionFactory->create($actionEntity);
        if ($actionExecutor->execute($actOnEntity, $user)) {
            // Log what just happened for troubleshooting
            $this->log->info(
                "WorkflowService->executeAction: successfully ran action " .
                    $actionEntity->getEntityId() .
                    " on " .
                    $actOnEntity->getEntityId()
            );

            // If action completed and returned true then run children
            $this->runChildActions($actionEntity, $actOnEntity, $user);
        } elseif ($actionExecutor->getLastError()) {
            // Log the error
            $this->log->error(
                "WorkflowService->executeAction: Failed to execute " .
                    $actionEntity->getEntityId() .
                    "on " .
                    $actOnEntity->getEntityId() .
                    " with error " .
                    $actionExecutor->getLastError()->getMessage()
            );
        }
    }

    /**
     * Run child actions for a given parent action
     *
     * Note: We keep this public because WaitCondition executors require
     * we pause the workflow. This is how we resume it.
     *
     * @param WorkflowActionEntity $parentAction
     * @param EntityInterface $actOnEntity
     * @param UserEntity $user
     * @return void
     */
    public function runChildActions(WorkflowActionEntity $parentAction, EntityInterface $actOnEntity, UserEntity $user)
    {
        // Log what just happened for troubleshooting
        $this->log->info(
            __CLASS__ . '->runChildActions: for ' .
                $parentAction->getEntityId()
        );

        // If action completed and returned true then run children
        $childActions = $this->workFlowDataMapper->getActions(
            $user->getAccountId(),
            $parentAction->getValue('workflow_id'),
            $parentAction->getEntityId()
        );

        foreach ($childActions as $childAction) {
            $this->executeAction($childAction, $actOnEntity, $user);
        }
    }
}
