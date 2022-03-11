<?php

namespace Netric\Workflow\ActionExecutor;

use Netric\Entity\EntityInterface;
use Netric\Entity\ObjType\UserEntity;

/**
 * Workflow action to create a new entity
 */
class CreateEntityActionExecutor extends AbstractActionExecutor implements ActionExecutorInterface
{

    /**
     * Execute an action on an entity
     *
     * @param EntityInterface $actOnEntity The entity (any type) we are acting on
     * @param UserEntity $user The user who is initiating the action
     * @return bool true on success, false on failure
     */
    public function execute(EntityInterface $actOnEntity, UserEntity $user): bool
    {
        // This is not yet implemented, jsut fail for now
        return false;

        // Get merged params from the workflow action
        $createObjType = $this->getParam('obj_type', $actOnEntity);


        // Create a new object of type obj_type
        $createdEntity = $this->getEntityloader()->create($createObjType, $user->getAccountId);

        // Now loop through each field in the newly created entity, and see if
        // we can get a param from the action
        $fields = $actOnEntity->getDefinition()->getFields();
        foreach ($fields as $field) {
            $fieldValueFromParam = $this->getParam($field->getName(), $actOnEntity);
            if ($fieldValueFromParam !== null) {
                $createdEntity->setValue($field->getName(), $fieldValueFromParam);
            }
        }

        return ($this->getEntityloader()->save($createdEntity, $user)) ? true : false;
    }
}
