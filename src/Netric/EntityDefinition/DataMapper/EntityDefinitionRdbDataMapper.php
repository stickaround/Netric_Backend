<?php

namespace Netric\EntityDefinition\DataMapper;

use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityDefinition\Field;
use Netric\Permissions\Dacl;
use Netric\Db\Relational\RelationalDbContainerInterface;
use Netric\Db\Relational\RelationalDbContainer;
use Netric\Db\Relational\RelationalDbInterface;
use Netric\Db\Relational\Exception\DatabaseQueryException;
use Netric\WorkerMan\WorkerService;
use Aereus\Config\Config;
use Ramsey\Uuid\Uuid;
use RuntimeException;

/**
 * Load and save entity definition data to a relational database
 */
class EntityDefinitionRdbDataMapper extends EntityDefinitionDataMapperAbstract implements EntityDefinitionDataMapperInterface
{
    /**
     * Entity type table
     */
    const ENTITY_TYPE_TABLE = 'entity_definition';

    /**
     * Database container
     *
     * @var RelationalDbContainerInterface
     */
    private $databaseContainer = null;

    /**
     * Construct and initialize dependencies
     *
     * @param RelationalDbContainer $dbContainer Handles the database actions
     */
    public function __construct(
        RelationalDbContainer $dbContainer,
        WorkerService $workerService,
        Config $config
    ) {
        $this->databaseContainer = $dbContainer;

        parent::__construct($workerService, $config);
    }

    /**
     * Get active database handle
     *
     * @param string $accountId The account being acted on
     * @return RelationalDbInterface
     */
    private function getDatabase(string $accountId): RelationalDbInterface
    {
        return $this->databaseContainer->getDbHandleForAccountId($accountId);
    }

    /**
     * Open an object definition by name
     *
     * @param string $objType The name of the object type
     * @param string $accountId The account that owns the entity definition
     *
     * @return EntityDefinition
     */
    public function fetchByName(string $objType, string $accountId)
    {
        if (!$objType || !is_string($objType)) {
            throw new \RuntimeException('objType is a required param');
        }

        $def = new EntityDefinition($objType, $accountId);

        // Get basic object definition
        // ------------------------------------------------------
        $sql = "select
            entity_definition_id, def_data, revision, title,
			f_system, system_definition_hash, dacl, capped,
            default_activity_level, is_private, store_revisions,
            recur_rules, inherit_dacl_ref, parent_field, uname_settings,
            list_title, icon, system_definition_hash
            from " . self::ENTITY_TYPE_TABLE . " 
            where name=:name AND account_id=:account_id";
        $result = $this->getDatabase($accountId)->query($sql, [
            'name' => $objType,
            'account_id' => $accountId
        ]);

        if ($result->rowCount()) {
            $row = $result->fetch();
            $defData = json_decode($row['def_data'], true);
            $def->fromArray($defData);

            if ($row['entity_definition_id'] && !$def->getEntityDefinitionId()) {
                $def->setEntityDefinitionId($row['entity_definition_id']);
            }

            return $def;
        }

        return null;
    }

    /**
     * Get an entity definition by id
     *
     * @param string $definitionTypeId Object type of the defintion
     * @param string $accountId The account that owns the entity definition
     *
     * @return EntityDefinition|null
     */
    public function fetchById(string $definitionTypeId, string $accountId): ?EntityDefinition
    {
        $sql = 'SELECT name FROM ' . self::ENTITY_TYPE_TABLE . ' WHERE entity_definition_id=:entity_definition_id AND account_id=:account_id';
        $result = $this->getDatabase($accountId)->query($sql, [
            'entity_definition_id' => $definitionTypeId,
            'account_id' => $accountId
        ]);

        // The object was not found
        if ($result->rowCount() === 0) {
            return null;
        }

        // Load rows and set values in the entity
        $row = $result->fetch();

        // Load by name
        return $this->fetchByName($row['name'], $accountId);
    }

    /**
     * Delete object definition
     *
     * @param EntityDefinition $def The definition to delete
     * @return bool true on success, false on failure
     */
    public function deleteDef(EntityDefinition $def)
    {
        // System objects cannot be deleted
        if ($def->getSystem()) {
            throw new RuntimeException('Unable to delete a system definition');
        }

        // Only delete existing types of course
        if (!$def->getEntityDefinitionId()) {
            return false;
        }

        // Delete object type entries from the database
        // $this->getDatabase($accountId)->delete(
        //     'app_object_type_fields',
        //     ['type_id' => $def->getEntityDefinitionId()]
        // ); // Will cascade

        // Get the account id from the definition
        $accountId = $def->getAccountId();

        $this->getDatabase($accountId)->delete(
            self::ENTITY_TYPE_TABLE,
            ['entity_definition_id' => $def->getEntityDefinitionId()]
        );

        return true;
    }

    /**
     * Save a definition
     *
     * @param EntityDefinition $def The definition to save
     * @return string|bool entity id on success, false on failure
     */
    public function saveDef(EntityDefinition $def)
    {
        // Get the account id from the definition
        $accountId = $def->getAccountId();

        // Define type update
        $data = [
            "account_id" => $accountId,
            "name" => $def->getObjType(),
            "title" => $def->title,
            "revision" => $def->revision,
            "f_system" => $def->system,
            "application_id" => ($def->applicationId) ? $def->applicationId : null,
            "capped" => ($def->capped) ? $def->capped : null,
            "dacl" => ($def->getDacl()) ? json_encode(($def->getDacl()->toArray())) : null,
            "default_activity_level" => ($def->defaultActivityLevel) ? $def->defaultActivityLevel : null,
            "is_private" => $def->isPrivate,
            "store_revisions" => $def->storeRevisions,
            "recur_rules" => ($def->recurRules) ? json_encode($def->recurRules) : null,
            "inherit_dacl_ref" => ($def->inheritDaclRef) ? "'" . $def->inheritDaclRef : null,
            "parent_field" => ($def->parentField) ? $def->parentField : null,
            "uname_settings" => ($def->unameSettings) ? $def->unameSettings : null,
            "list_title" => ($def->listTitle) ? $def->listTitle : null,
            "icon" => ($def->icon) ? $def->icon : null,
            "system_definition_hash" => ($def->systemDefinitionHash) ? $def->systemDefinitionHash : null,
            "def_data" => json_encode($def->toArray()),
        ];

        $appObjectTypeId = $def->getEntityDefinitionId();
        if ($appObjectTypeId) {
            $this->getDatabase($accountId)->update(self::ENTITY_TYPE_TABLE, $data, [
                "entity_definition_id" => $appObjectTypeId,
                "account_id" => $accountId,
            ]);
        } else {
            // Create new uuid
            $data["entity_definition_id"] = Uuid::uuid4()->toString();
            $this->getDatabase($accountId)->insert(
                self::ENTITY_TYPE_TABLE,
                $data
            );

            $def->setEntityDefinitionId($data["entity_definition_id"]);
        }

        return true;

        // Check to see if this dynamic object has yet to be initilized
        // $this->createObjectTable($def->getObjType(), $def->getEntityDefinitionId());

        // // Save and create fields
        // $this->saveFields($def);

        // // Associate with applicaiton if set
        // if ($def->applicationId) {
        //     $this->associateWithApp($def, $def->applicationId);
        // }
    }

    /**
     * Object tables are created dynamically to inherit from the parent object table
     *
     * @param string $objType The type name of this table
     * @param int $typeId The unique id of the object type
     */
    private function createObjectTable($objType, $typeId)
    {
        // TODO: We no longer create tables for each object type
        return;
    }

    /**
     * Create a dynamic index for a field in this object type
     *
     * This is primarily used in /services/ObjectDynIdx.php to build
     * dynamic indexes from usage stats.
     *
     * @param EntityDefinition $def The EntityDefinition we are saving
     * @param Field The Field to verity we have a column for
     */
    public function createFieldIndex(EntityDefinition $def, Field $field)
    {
        // TODO: We no longer do this as a standard entity defintiion rocess
        // later we will probably handle it by looking at the number of entities
        // that exist for a given object type, then creating index only on
        // indexed fields (in the entity definition)
        return true;
    }

    /**
     * Associate an object with an application
     *
     * @param EntityDefintion $def The definition to associate with an application
     * @param string $applicationId The unique id of the application we are associating with
     * @return bool true on success, false on failure
     */
    public function associateWithApp(EntityDefinition $def, $applicatoinId)
    {
        // Get the account id from the definition
        $accountId = $def->getAccountId();

        $otid = $def->getEntityDefinitionId();

        $sql = "select id from application_objects where application_id=:application_id and entity_definition_id=:entity_definition_id";
        $result = $this->getDatabase($accountId)->query($sql, ['application_id' => $applicatoinId, "entity_definition_id" => $otid]);

        if ($result->rowCount()) {
            $this->getDatabase($accountId)->insert(
                "application_objects",
                [
                    "application_id" => $applicatoinId,
                    "entity_definition_id" => $otid
                ]
            );
        }
    }

    /**
     * Get all the entity object types
     * @param string $accountId The account that owns the entity definition
     *
     * @return array Collection of objects
     */
    public function getAllObjectTypes(string $accountId)
    {
        $sql = "select name from " . self::ENTITY_TYPE_TABLE . " where account_id=:account_id";
        $result = $this->getDatabase($accountId)->query($sql, ['account_id' => $accountId]);

        foreach ($result->fetchAll() as $row) {
            $ret[] = $row['name'];
        }

        return $ret;
    }
}
