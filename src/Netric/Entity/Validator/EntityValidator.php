<?php

/**
 * Manage entity forms
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\Entity\Validator;

use Netric\Entity\Entity;
use Netric\Entity\DataMapperInterface;
use Netric\Error;

/**
 * Class for validating entities
 *
 * This is mostly used in the DataMapperAbstract::save function
 * to validate basic conditions are correct before writing.
 *
 * @package Netric\Entity
 */
class EntityValidator implements Error\ErrorAwareInterface
{
    /**
     * Validation errors
     *
     * @var Error\Error[]
     */
    private $errors = [];

    /**
     * Setup the validator service
     */
    public function __construct()
    {
    }

    /**
     * Determine if an entity is valid by checking various conditions
     *
     * Conditions include:
     *  - uname is unique
     *  - All required fields have values (?)
     *  - If a field is marked as 'unique' then make sure it is unique combined with parentId
     *
     * @param Entity $entity
     * @param DataMapperInterface $entityDataMapper
     * @return bool
     */
    public function isValid(Entity $entity, DataMapperInterface $entityDataMapper)
    {
        // Check if a manually set unique name is unique
        if (!$this->uniqueNameIsUnique($entity, $entityDataMapper)) {
            $this->errors[] = "Unique name " . $entity->getValue('uname') . " is not unique";
            return false;
        }

        return true;
    }

    /**
     * Get the last error logged
     *
     * @return Error\Error
     */
    public function getLastError()
    {
        return $this->errors[count($this->errors) - 1];
    }

    /**
     * Get all errors
     *
     * @return Error\Error[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Check that a manually set uname of an entity is unique
     */
    private function uniqueNameIsUnique(Entity $entity, DataMapperInterface $entityDataMapper)
    {
        // Default to true if there is no uname
        if (!$entity->getValue('uname')) {
            return true;
        }

        // Check to see if the uname was previously checked
        if ($entity->getEntityid() && !$entity->fieldValueChanged('uname')) {
            return true;
        }

        // Uname was set for a new entity or changed, verify it
        return $entityDataMapper->verifyUniqueName($entity, $entity->getValue('uname'));
    }
}
