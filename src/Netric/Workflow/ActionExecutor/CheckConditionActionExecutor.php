<?php

declare(strict_types=1);

namespace Netric\Workflow\ActionExecutor;

use Netric\Entity\ObjType\WorkflowActionEntity;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\EntityQuery\EntityQuery;

/**
 * Action used to check if conditions match for the entity being acted on
 *
 * Params in the 'data' field:
 *
 * 'conditions' => [
 *      [
 *          'blogic'=>EntityQuery\Where::COMBINED_BY_*,
 *          'field_name'='name_of_entity_field',
 *          'operator'=>EntityQuery\Where::OPERATOR_*
 *          'value'=>'The value the operator is comparing with'
 *      ]
 * ]
 */
class CheckConditionActionExecutor extends AbstractActionExecutor implements ActionExecutorInterface
{
    /**
     * Entity index for running queries against
     */
    private IndexInterface $entityIndex;

    /**
     * This must be called by all derived classes
     *
     * @param EntityLoader $entityLoader
     * @param WorkflowActionEntity $actionEntity
     * @param string $appliactionUrl
     */
    public function __construct(
        EntityLoader $entityLoader,
        WorkflowActionEntity $actionEntity,
        string $applicationUrl,
        IndexInterface $entityIndex
    ) {
        $this->entityIndex = $entityIndex;

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
        // Entity must be saved to meet conditions
        if (!$actOnEntity->getEntityId()) {
            return false;
        }

        // Get merged params
        $conditions = $this->getParam('conditions', $actOnEntity);

        // We use the index for checking if conditions match since it
        // contains all the condition logic which can get pretty complex
        $query = new EntityQuery($actOnEntity->getDefinition()->getObjType(), $user->getAccountId(), $user->getEntityId());

        // Add the entity as a condition to see if it meets the criteria
        $query->where("entity_id")->equals($actOnEntity->getEntityId());

        // Query deleted if the entity is deleted
        if ($actOnEntity->isArchived()) {
            $query->andWhere("f_deleted")->equals(true);
        }

        // Add conditions
        if (is_array($conditions)) {
            foreach ($conditions as $cond) {
                $query->andWhere($cond['field_name'], $cond['operator'], $cond['value']);
            }
        }

        // Did the entity meet the conditions?
        $result = $this->entityIndex->executeQuery($query);
        return ($result->getNum()) ? true : false;
    }
}
