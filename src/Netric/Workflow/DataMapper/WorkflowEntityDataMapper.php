<?php

declare(strict_types=1);

namespace Netric\Workflow\DataMapper;

use Netric\Entity\EntityLoader;
use Netric\Entity\EntityInterface;
use Netric\Entity\ObjType\WorkflowEntity;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\EntityQuery\EntityQuery;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Workflow\Action\ActionExecutorFactory;
use RuntimeException;

/**
 * Load workflows entities for the workflow system
 */
class WorkflowEntityDataMapper implements WorkflowDataMapperInterface
{
    /**
     * Entity loader for loading up the entity being acted on
     *
     * @var EntityLoader
     */
    private $entityLoader = null;

    /**
     * Index used for querying entities - mostly actions
     *
     * @var IndexInterface
     */
    private $entityIndex = null;

    /**
     * Construct the Workflow DataMapper
     *
     * @param EntityLoader $entityLoader Entity loader for loading up the entities
     * @param IndexInterface $entityIndex Index used for querying entities - mostly actions
     * @param ActionExecutorFactory $actionFactory Factory to create new actions
     */
    public function __construct(
        EntityLoader $entityLoader,
        IndexInterface $entityIndex
    ) {
        $this->entityLoader = $entityLoader;
        $this->entityIndex = $entityIndex;
    }

    /**
     * Get a list of Workflows as an array
     *
     * @param string $entityType If set only get for a specific entity defintion type
     * @param string $accountId The account we are getting workflows fro
     * @param string $eventName Workflows listening for this specific event
     * @return WorkflowEntity[] An array of WorkflowEntity(ies) or just an empty array if none found
     */
    public function getActiveWorkflowsForEvent(string $entityType, string $accountId, string $eventName): array
    {
        // Query workflows
        $query = new EntityQuery(ObjectTypes::WORKFLOW, $accountId);
        $query->andWhere("f_active")->equals(true);
        $query->andWhere("object_type")->equals($entityType);

        // Add event filter
        switch ($eventName) {
            case WorkflowEntity::EVENT_CREATE:
                $query->andWhere("f_on_create")->equals(true);
                break;
            case WorkflowEntity::EVENT_UPDATE:
                $query->andWhere("f_on_update")->equals(true);
                break;
            case WorkflowEntity::EVENT_DELETE:
                $query->andWhere("f_on_delete")->equals(true);
                break;
            case WorkflowEntity::EVENT_DAILY:
                $query->andWhere("f_on_daily")->equals(true);
                $yesterday = date("Y-m-d H:i:s T", strtotime("-1 day"));
                $query->andWhere("ts_lastrun")->isLessOrEqualTo($yesterday);
                $query->orWhere("ts_lastrun")->equals(null);
                break;
        }

        $result = $this->entityIndex->executeQuery($query);
        if (!$result) {
            throw new RuntimeException(
                "Could not get actions: " .
                    $this->entityIndex->getLastError()
            );
        }

        $workflows = [];
        $num = $result->getNum();
        for ($i = 0; $i < $num; $i++) {
            $workflows[] = $result->getEntity($i);
        }

        return $workflows;
    }

    /**
     * Get workflow actions for a workflow (root leve) or child actions of a parent action
     *
     * @param string $accountId
     * @param string $workflowId
     * @param string $parentActionId
     * @return getActions[]
     */
    public function getActions(string $accountId, string $workflowId, string $parentActionId = ''): array
    {
        // Query all actions
        $query = new EntityQuery(ObjectTypes::WORKFLOW_ACTION, $accountId);
        $query->andWhere("workflow_id")->equals($workflowId);
        if ($parentActionId) {
            $query->andWhere("parent_action_id")->equals($parentActionId);
        }

        $result = $this->entityIndex->executeQuery($query);
        if (!$result) {
            throw new RuntimeException("Could not get actions: " . $this->entityIndex->getLastError());
        }

        $actions = [];
        $num = $result->getNum();
        for ($i = 0; $i < $num; $i++) {
            $actions[] = $result->getEntity($i);
        }

        return $actions;
    }

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
    ): EntityInterface {
        $workflowInstance = $this->entityLoader->create(ObjectTypes::WORKFLOW_INSTANCE, $user->getAccountId());
        $workflowInstance->setValue('entity_definition_id', $actOnEntity->getDefinition()->getEntityDefinitionId());
        $workflowInstance->setValue('object_type', $actOnEntity->getDefinition()->getObjType());
        $workflowInstance->setValue('entity_id', $actOnEntity->getEntityId());
        $workflowInstance->setValue('workflow_id', $workflow->getEntityId());
        $workflowInstance->setValue('ts_started', 'now');

        // Save it
        $this->entityLoader->save($workflowInstance, $user);
        return $workflowInstance;
    }

    /**
     * Get past instances that were run of a given workflow for an entity
     *
     * @param WorkflowEntity $workflow
     * @param EntityInterface $actOnEntity
     * @return EntityInterface[]
     */
    public function getInstancesForEntity(WorkflowEntity $workflow, EntityInterface $actOnEntity): array
    {
        $query = new EntityQuery(ObjectTypes::WORKFLOW_INSTANCE, $actOnEntity->getAccountId());
        $query->andWhere("workflow_id")->equals($workflow->getEntityId());
        $query->andWhere('entity_id')->equals($actOnEntity->getEntityId());
        $result = $this->entityIndex->executeQuery($query);
        if (!$result) {
            throw new RuntimeException(
                "Could not get instances: " .
                    $this->entityIndex->getLastError()
            );
        }

        $instances = [];
        $num = $result->getNum();
        for ($i = 0; $i < $num; $i++) {
            $instances[] = $result->getEntity($i);
        }

        return $instances;
    }
}
