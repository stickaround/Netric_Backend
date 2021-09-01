<?php

declare(strict_types=1);

namespace Netric\Workflow\DataMapper;

use Netric\Entity\EntityInterface;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\ObjType\WorkflowActionEntity;
use Netric\Entity\ObjType\WorkflowEntity;

/**
 * Interface WorkflowDataMapperInterface
 */
interface WorkflowDataMapperInterface
{
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

    /**
     * Get past instances that were run of a given workflow for an entity
     *
     * @param WorkflowEntity $workflow
     * @param EntityInterface $actOnEntity
     * @return EntityInterface[]
     */
    public function getInstancesForEntity(WorkflowEntity $workflow, EntityInterface $actOnEntity): array;
}
