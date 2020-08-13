<?php

declare(strict_types=1);

namespace Netric\Workflow;

use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\ObjType\WorkflowEntity;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\Workflow\ActionExecutor\ActionExecutorFactory;
use Netric\Entity\EntityInterface;
use Netric\Entity\ObjType\WorkflowActionEntity;
use Netric\Workflow\DataMapper\WorkflowDataMapperInterface;
use Netric\Log\Log;
use Netric\Log\LogInterface;

/**
 * Workflow service
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
     * @param Log $log Netric log
     */
    public function __construct(
        WorkflowDataMapperInterface $workFlowDataMapper,
        ActionExecutorFactory $actionFactory,
        Log $log
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
     * @return string[] ID of each workflow instance started
     */
    public function runWorkflowsOnEvent(EntityInterface $entity, string $eventName, UserEntity $user): array
    {
        $entityType = $entity->getDefinition()->getObjType();
        $activeWorkflows = $this->workFlowDataMapper->getActiveWorkflowsForEvent(
            $entityType,
            $user->getAccountId(),
            $eventName
        );

        $instancesStarted = [];
        foreach ($activeWorkflows as $workflow) {
            // Start an instance
            $instancesStarted[] = $this->startWorkflowInstance($workflow, $entity, $user);
        }

        return $instancesStarted;
    }

    /**
     * Run actions that were scheduled by a workflow instance
     */
    public function runScheduledActions()
    {
        // /*
        //  * Get array of instances and actions that are scheduled to run on
        //  * or before this moment.
        //  */
        // $scheduled = $this->workFlowDataMapper->getScheduledActions();
        // foreach ($scheduled as $queued) {
        //     $workFlowInstance = $queued['instance'];
        //     $action = $queued['action'];
        //     $this->executeAction($action, $workFlowInstance, true);
        // }

        // // Log what just happened
        // $this->log->info("Found and executed " . count($scheduled) . " scheduled actions");
    }

    /**
     * Start a workflow instance given a WorkFlowLegacy and an Entity
     *
     * @param WorkflowEntity $workflow The WorkFlowLegacy we would like to run
     * @param EntityInterface $entity The entity we are running on
     * @param UserEntity $user
     */
    private function startWorkflowInstance(WorkflowEntity $workflow, EntityInterface $entity, UserEntity $user)
    {
        // Create a new instance for this workflow and entity
        // $workflowInstance = $this->workFlowDataMapper->createWorkflowInstance(
        //     $workflow,
        //     $entity,
        //     $user
        // );

        // Now execute first level of actions in the workflow
        $actions = $this->workFlowDataMapper->getActions($user->getAccountId(), $workflow->getEntityId());
        foreach ($actions as $action) {
            $this->executeAction($action, $entity, $user);
        }
    }

    /**
     * Execute an action for a workflow instance
     *
     * @param WorkflowActionEntity $action Entity with action state
     * @param EntityInterface $actOnEntity The entity we are acting on
     * @param UserEntity $user
     */
    private function executeAction(WorkflowActionEntity $actionEntity, EntityInterface $actOnEntity, UserEntity $user)
    {
        // Get the action executor - we split this because the entity state for action is pretty
        // generic but the execution is highly dynamic and specific.
        $actionExecutor = $this->actionFactory->create($actionEntity->getValue('type_name'));
        if ($actionExecutor->execute($actionEntity, $actOnEntity, $user)) {
            // Log what just happened for troubleshooting
            $this->log->info(
                "Executed action " .
                    $actionEntity->getEntityId() .
                    " against instance " .
                    $workflowInstance->getEntityId()
            );

            // TODO: Not sure why we did this initially
            // // Delete any scheduled tasks if set
            // if ($purgeScheduled) {
            //     $this->workFlowDataMapper->deleteScheduledAction(
            //          $workflowInstance->getEntityId(),
            //          $actionEntity->getEntityId()
            //      );
            // }

            // If action completed and returned true then run children
            $childActions = $this->workFlowDataMapper->getActions(
                $user->getAccountId(),
                $workflowInstance->getValue('workflow_id'),
                $actionEntity->getEntityId()
            );
            foreach ($childActions as $childAction) {
                $this->executeAction($childAction, $workflowInstance, $user);
            }
        } elseif ($actionExecutor->getLastError()) {
            // Log the error
            $this->log->error(
                "Failed to execute " .
                    $actionEntity->getEntityId() .
                    "(" . $actionEntity->getEntityId() . "): " .
                    $actionExecutor->getLastError()->getMessage()
            );
        }
    }
}
