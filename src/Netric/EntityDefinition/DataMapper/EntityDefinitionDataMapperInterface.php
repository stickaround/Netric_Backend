<?php
namespace Netric\EntityDefinition\DataMapper;

use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityDefinition\Field;
use Netric\Account\Account;

/**
 * Interface used for managing EntityDefinition state from a DB
 */
interface EntityDefinitionDataMapperInterface
{
    /**
     * Open an object definition by name
     *
     * @var string $objType The name of the object type
     * @var string $id The Id of the object
     * @return DomainEntity
     */
    public function fetchByName($objType);

    /**
     * Delete object definition
     *
     * @param EntityDefinition $def The definition to delete
     * @return bool true on success, false on failure
     */
    public function delete(EntityDefinition $def);

    /**
     * Save a definition
     *
     * @param EntityDefinition $def The definition to save
     * @return string|bool entity id on success, false on failure
     */
    public function save(EntityDefinition $def);

    /**
     * Associate an object with an application
     *
     * @param EntityDefinition $def The definition to associate with an application
     * @param string $applicationId The unique id of the application we are associating with
     * @return bool true on success, false on failure
     */
    public function associateWithApp(EntityDefinition $def, $applicatoinId);

    /**
     * Create a dynamic index for a field in this object type
     *
     * This is primarily used in /services/ObjectDynIdx.php to build
     * dynamic indexes from usage stats.
     *
     * @param EntityDefinition $def The EntityDefinition we are saving
     * @param Field The Field to verity we have a column for
     */
    public function createFieldIndex(EntityDefinition $def, Field $field);


    /**
     * Delete an object definition by name
     *
     * @var string $objType The name of the object type
     * @return bool true on success, false on failure
     */
    public function deleteByName($objType);

    /**
     * Get current account
     *
     * @return Account
     */
    public function getAccount();

    /**
     * Get all the entity object types
     *
     * @return array Collection of objects
     */
    public function getAllObjectTypes();
}