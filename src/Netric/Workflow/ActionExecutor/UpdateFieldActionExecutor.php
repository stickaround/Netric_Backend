<?php

declare(strict_types=1);

namespace Netric\Workflow\ActionExecutor;

use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\EntityInterface;
use Netric\EntityDefinition\Field;
use Netric\Error\Error;

/**
 * Action to update the field of an entity
 */
class UpdateFieldActionExecutor extends AbstractActionExecutor implements ActionExecutorInterface
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
        // Get merged params
        $updateField = $this->getParam('update_field', $actOnEntity);
        $updateValue = $this->getParam('update_value', $actOnEntity);

        if (!$updateField) {
            $this->addError(new Error("Could not update field because update_field param was not set"));
            return false;
        }

        // Get the field we are updating
        $field = $actOnEntity->getDefinition()->getField($updateField);
        if (!$field) {
            $this->addError(new Error("Tried to update a field that does not exist: " . $updateField));
            return false;
        }

        // Update the field
        $this->setEntityValue($actOnEntity, $field->type, $updateField, $updateValue);

        // Save changes
        $this->getEntityloader()->save($actOnEntity, $user);

        return true;
    }

    /**
     * Handle setting the entity value based on the field type
     *
     * @param EntityInterface $entity
     * @param string $fieldName
     * @param mixed $value
     * @return void
     */
    private function setEntityValue(EntityInterface $entity, string $fieldType, string $fieldName, $value): void
    {
        // Check if we are dealing with a multi value field
        if ($fieldType == Field::TYPE_GROUPING_MULTI || $fieldType == Field::TYPE_OBJECT_MULTI) {
            $entity->addMultiValue($fieldName, $value);
            return;
        }

        // Default to single value set
        $entity->setValue($fieldName, $value);
    }
}
