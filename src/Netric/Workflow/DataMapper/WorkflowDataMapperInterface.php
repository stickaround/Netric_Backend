<?php

declare(strict_types=1);

namespace Netric\Workflow\DataMapper;

use Netric\Entity\EntityInterface;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\ObjType\WorkflowActionEntity;
use Netric\Entity\ObjType\WorkflowEntity;
use Netric\Workflow\Workflow;

/**
 * Interface WorkflowDataMapperInterface
 */
interface WorkflowDataMapperInterface
{
    /**
     * Open a new workflow by id
     *
     * @param string $workflowId The unique id of the workflow to load
     * @return Workflow|null Returns null if $id does not exist
     */
    public function getWorkflowById(string $workflowId): ?WorkflowEntity;

    /**
     * Get a list of Workflows as an array
     *
     * @param string $entityType If set only get for a specific entity defintion type
     * @param string $accountId The account we are getting workflows fro
     * @param string $eventName Workflows listening for this specific event
     * @return WorkflowEntity[] An array of WorkflowEntity(ies) or just an empty array if none found
     */
    public function getActiveWorkflowsForEvent(string $entityType, string $accountId, string $eventName): array;

    /**
     * Schedule an action to run at some time in the future
     *
     * @param int $workFlowInstanceId
     * @param int $actionId
     * @param \DateTime $executeTime
     * @return bool true on success, false on failure
     */
    public function scheduleAction($workFlowInstanceId, $actionId, \DateTime $executeTime);

    /**
     * Delete a scheduled action if set for a workflow instance and an action
     *
     * @param int $workFlowInstanceId
     * @param int $actionId
     * @return bool true on success, false on failure
     */
    public function deleteScheduledAction($workFlowInstanceId, $actionId);

    /**
     * Get a scheduled action time if set for a workflow instance and an action
     *
     * @param int $workFlowInstanceId
     * @param int $actionId
     * @return \DateTime|null
     */
    public function getScheduledActionTime($workFlowInstanceId, $actionId);

    /**
     * Get all actions scheduled to be executed on or before $toDate
     *
     * @param \DateTime $toDate
     * @return array(array("instance"=>WorkFlowLegacyInstance, "action"=>ActionInterface))
     */
    public function getScheduledActions(\DateTime $toDate = null);


    /**
     * Get workflow actions for a workflow (root leve) or child actions of a parent action
     *
     * @param string $accountId
     * @param string $workflowId
     * @param string $parentActionId
     * @return WorkflowActionEntity[]
     */
    public function getActions(string $accountId, string $workflowId, string $parentActionId = ''): array;

    /**
     * Create a new workflow instance on the $actOnEntity
     *
     * @param WorkflowEntity $workflow
     * @param EntityInterface $actOnEntity
     * @param UserEntity $user
     * @return EntityInterface
     */
    public function createWorkflowInstance(
        WorkflowEntity $workflow,
        EntityInterface $actOnEntity,
        UserEntity $user
    ): EntityInterface;
}
