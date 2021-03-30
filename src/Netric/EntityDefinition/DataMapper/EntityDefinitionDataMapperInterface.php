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
     * @param string $objType The name of the object type
     * @param string $accountId The account that owns the entity definition
     *
     * @return EntityDefinition
     */
    public function fetchByName(string $objType, string $accountId);

    /**
     * Get an entity definition by id
     *
     * @param string $definitionTypeId Object type of the defintion
     * @param string $accountId The account that owns the entity definition
     *
     * @return EntityDefinition
     */
    public function fetchById(string $definitionTypeId, string $accountId): ?EntityDefinition;

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
     * @param string $applicatoinId The unique id of the application we are associating with
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
     * @param Field $field The Field to verity we have a column for
     */
    public function createFieldIndex(EntityDefinition $def, Field $field);


    /**
     * Delete an object definition by name
     *
     * @param string $objType The name of the object type
     * @param string $accountId The account that owns the entity definition
     *
     * @return bool true on success, false on failure
     */
    public function deleteByName(string $objType, string $accountId);

    /**
     * Get current account
     *
     * @return Account
     */
    public function getAccount();

    /**
     * Get all the entity object types
     *
     * @param string $accountId The account that owns the entity definition
     * @return array Collection of objects
     */
    public function getAllObjectTypes(string $accountId);

    /**
     * Get the latest hash for a system definition from the file system
     *
     * This is often used for cache breaking in loaders
     *
     * @param string $objType
     * @return string
     */
    public function getLatestSystemDefinitionHash(string $objType);

    /**
     * Update a definition from the local system in data/entity_definitions
     *
     * @param EntityDefinition $def
     * @throws \InvalidArgumentException If a non-system definition is passed in
     */
    public function updateSystemDefinition(EntityDefinition $def);

    /**
     * If an error was encountered, return the reason
     *
     * @return string
     */
    public function getLastError();
}
