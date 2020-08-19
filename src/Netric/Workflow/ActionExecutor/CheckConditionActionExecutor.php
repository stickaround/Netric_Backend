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

    // Below is the old conditions match we once used for the overall workflow
    //    /**
    //     * Test to see if an entity matches a set of WorkFlowLegacy conditions
    //     *
    //     * @param WorkflowEntity $workflow A workflow that we might run if the conditions match
    //     * @param EntityInterface $entity The entity we are checking against the workflow conditions
    //     * @param UserEntity $user
    //     * @return bool true if the entity matches, or false if it does not
    //     */
    //    private function workFlowConditionsMatch(WorkflowEntity $workflow, EntityInterface $entity, UserEntity $user)
    //    {
    //        // We use the index for checking if conditions match since it contains all the condition logic
    //        $query = new EntityQuery($entity->getDefinition()->getObjType());
    //
    //        // Add the entity as a condition to see if it meets the criteria
    //        $query->where("entity_id")->equals($entity->getEntityId());
    //
    //        // Query deleted if the entity is deleted
    //        if ($entity->isArchived()) {
    //            $query->andWhere("f_deleted")->equals(true);
    //        }
    //
    //        /*
    //         * If the workflow has a onlyOnConditionsUnmet flag then we
    //         * need to check to see if any of the fields that match conditions
    //         * were changed (presumably to match the conditions) before we trigger the
    //         * workflow. This is useful in cases where we check if something like
    //         * task done='t' then send email, but if the user just hits save for notes
    //         * we don't want to send another email about it being completed.
    //         * However, if they update the task to mark it as incomplete for some reason,
    //         * then later complete it again, we do want to trigger the notification.
    //         */
    //        $fieldChanged = false;
    //
    //        // Get where conditions from the workflow
    //        $conditionText = $workflow->getValue('conditions');
    //        if ($conditionText) {
    //            $conditionData = json_decode($conditionText, true);
    //            foreach ($conditionData as $condArr) {
    //                $cond = new EntityQuery\Where();
    //                $cond->fromArray($condArr);
    //                $query->andWhere($cond->fieldName, $cond->operator, $cond->value);
    //                if ($entity->fieldValueChanged($cond->fieldName)) {
    //                    $fieldChanged = true;
    //                }
    //            }
    //        }
    //
    //        // Get results
    //        $result = $this->entityIndex->executeQuery($query);
    //        $num = $result->getNum();
    //
    //        // See comments above for $fieldChanged variable explanation
    //        if ($workflow->getValue('f_condition_unmet') && !$fieldChanged) {
    //            return false;
    //        }
    //
    //        // If we found the entity in the query we know it is a match
    //        return ($num) ? true : false;
    //    }
}
