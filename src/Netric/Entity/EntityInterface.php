<?php

/**
 * All entities/objects should implement this interface
 */

namespace Netric\Entity;

use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\EntityDefinition;

interface EntityInterface
{
    /**
     * Get the object type of this object
     *
     * @return string
     */
    public function getObjType();

    /**
     * Get unique id of this object
     */
    public function getEntityId(): string;


    /**
     * Every entity must have an accountId
     *
     * @return string
     */
    public function getAccountId(): string;

    /**
     * Get definition
     *
     * @return EntityDefinition
     */
    public function getDefinition(): EntityDefinition;

    /**
     * Return either the string or an array of values if *_multi
     *
     * @param string $strname
     * @return string|array
     */
    public function getValue($strname);

    /**
     * Get fkey name for key/value field types like fkey and fkeyMulti
     *
     * @param string $strName The name of the field to pull
     * @param string $id If set, get the label for the id
     * @return string
     */
    public function getValueName($strName, $id = null);

    /**
     * Get fkey name array for key/value field types like fkey and fkeyMulti
     *
     * @param string $strName The name of the field to pull
     * @return array(array("id"=>"name"))
     */
    public function getValueNames($strName);

    /**
     * Set a field value for this object
     *
     * @param string $strName
     * @param mixed $value
     * @param string $valueName If this is an object or fkey then cache the foreign value
     */
    public function setValue($strName, $value, $valueName = null);

    /**
     * Add a multi-value entry to the *_multi type field
     *
     * @param string $strName
     * @param string|int $value
     * @param string $valueName Optional value name if $value is a key
     */
    public function addMultiValue($strName, $value, $valueName = "");

    /**
     * Remove a value from a *_multi type field
     *
     * @param string $strName
     * @param string|int $value
     */
    public function removeMultiValue($strName, $value);

    /**
     * Set values from array
     *
     * @param array $data Associative array of values
     */
    public function fromArray($data);

    /**
     * Get all values and return them as an array
     *
     * @return array Associative array of all fields in array(field_name=>value) format
     */
    public function toArray();

    /**
     * Callback function used for derrived subclasses
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @param UserEntity $user The user that is acting on this entity
     */
    public function onBeforeSave(ServiceLocatorInterface $serviceLocator, UserEntity $user);

    /**
     * Callback function used for derrived subclasses
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @param UserEntity $user The user that is acting on this entity
     */
    public function onAfterSave(ServiceLocatorInterface $serviceLocator, UserEntity $user);

    /**
     * Callback function used for derrived subclasses
     *
     * @param ServiceLocatorInterface $serviceLocator Service manager used to load supporting services
     * @param UserEntity $user The user that is acting on this entity
     */
    public function onBeforeDeleteHard(ServiceLocatorInterface $serviceLocator, UserEntity $user);

    /**
     * Callback function used for derrived subclasses
     *
     * @param ServiceLocatorInterface $serviceLocator Service manager used to load supporting services
     * @param UserEntity $user The user that is acting on this entity
     */
    public function onAfterDeleteHard(ServiceLocatorInterface $serviceLocator, UserEntity $user);

    /**
     * Check if a field value changed since created or opened
     *
     * @param string $checkfield The field name
     * @return bool true if it is dirty, false if unchanged
     */
    public function fieldValueChanged($checkfield);

    /**
     * Reset is dirty indicating no changes need to be saved
     */
    public function resetIsDirty();

    /**
     * Check if the object values have changed
     *
     * @return true if object has been edited, false if not
     */
    public function isDirty();

    /**
     * Determine if this entity is unsaved / new
     *
     * @return bool true if the entity was previously saved to persistent storage
     */
    public function isSaved(): bool;

    /**
     * Get name of this object based on common name fields
     *
     * @return string The name/label of this object
     */
    public function getName();

    /**
     * Check if the archived/deleted flag is set for this entity but it still exists
     *
     * @return bool
     */
    public function isArchived(): bool;

    /**
     * Set defaults for a field given an event
     *
     * @param string $event The event we are firing
     * @param AntUser $user Optional current user for default variables
     */
    public function setFieldsDefault($event, $user = null);

    /**
     * Get the local recurrence pattern
     *
     * @return Recurrence\RecurrencePattern
     */
    public function getRecurrencePattern();

    /**
     * Try and get a textual description of this entity typically found in fileds named "notes" or "description"
     *
     * @return string The name of this object
     */
    public function getDescription(): string;

    /**
     * Get a textual representation of what changed
     */
    public function getChangeLogDescription(): string;

    /**
     * Get previous value of a changed field
     *
     * @param string $checkfield The field name
     * @return null if not found, mixed old value if set
     */
    public function getPreviousValue($checkfield);
}
