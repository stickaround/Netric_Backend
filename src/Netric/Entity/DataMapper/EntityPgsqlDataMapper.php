<?php

namespace Netric\Entity\DataMapper;

use Netric\Account\Account;
use Netric\Db\Relational\RelationalDbInterface;
use Netric\Entity\EntityInterface;
use Netric\Entity\Recurrence\RecurrenceIdentityMapper;
use Netric\EntitySync\Commit\CommitManager;
use Netric\EntityDefinition\EntityDefinitionLoader;
use Netric\EntityDefinition\Field;
use Netric\Entity\EntityFactory;
use Netric\Entity\Validator\EntityValidator;
use Netric\Db\Relational\RelationalDbContainerInterface;
use Netric\EntityGroupings\GroupingLoader;
use Netric\Entity\Entity;
use Netric\Db\Relational\RelationalDbContainer;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\WorkerMan\WorkerService;
use Ramsey\Uuid\Uuid;
use DateTime;
use Netric\PubSub\PubSubInterface;
use RuntimeException;

/**
 * Load and save entity data to a relational database
 */
class EntityPgsqlDataMapper extends EntityDataMapperAbstract implements EntityDataMapperInterface
{
    /**
     * Name of the tables where entity data is saved
     */
    const ENTITY_TABLE = 'entity';
    const ENTITY_REVISION_TABLE = 'entity_revision';
    const ENTITY_MOVED_TABLE = 'entity_moved';

    /**
     * Schema version used for migration to newer schemas as needed
     */
    const SCHEMA_VERSION = 2;

    /**
     * Database container
     *
     * @var RelationalDbContainerInterface
     */
    private $databaseContainer = null;

    /**
     * Class constructor
     *
     * @param Account $account The account being acted on
     */
    public function __construct(
        RecurrenceIdentityMapper $recurIdentityMapper,
        CommitManager $commitManager,
        EntityValidator $entityValidator,
        EntityFactory $entityFactory,
        EntityDefinitionLoader $entityDefLoader,
        GroupingLoader $groupingLoader,
        ServiceLocatorInterface $serviceManager,
        RelationalDbContainer $dbContainer,
        WorkerService $workerService,
        PubSubInterface $pubSub
    ) {
        // Pass in this aboslutely terrible list of dependencies
        parent::__construct(
            $recurIdentityMapper,
            $commitManager,
            $entityValidator,
            $entityFactory,
            $entityDefLoader,
            $groupingLoader,
            $serviceManager,
            $workerService,
            $pubSub
        );

        // Used to get active database connection for the right account
        $this->databaseContainer = $dbContainer;
    }

    /**
     * Get active database handle
     *
     * @param string $accountId
     * @return RelationalDbInterface
     */
    private function getDatabase(string $accountId): RelationalDbInterface
    {
        return $this->databaseContainer->getDbHandleForAccountId($accountId);
    }

    /**
     * Get entity data by guid
     *
     * @param string $entityId
     * @param string $accountid
     * @return array|null
     */
    protected function fetchDataByEntityId(string $entityId, string $accountId): ?array
    {
        $sql = 'SELECT entity_id, uname, field_data FROM ' . self::ENTITY_TABLE .
            ' WHERE entity_id = :entity_id AND account_id=:account_id';
        $result = $this->getDatabase($accountId)->query(
            $sql,
            ['entity_id' => $entityId, 'account_id' => $accountId]
        );

        // The object was not found
        if ($result->rowCount() === 0) {
            return null;
        }

        // Load rows and set values in the entity
        $row = $result->fetch();
        $entityData = json_decode($row['field_data'], true);

        /**
         * Override any of the json data with system column values
         * Some of these may be generated at update/insert so they could have
         * changed after the entity was exported and saved to the column
         */
        $entityData['entity_id'] = $row['entity_id'];
        $entityData['account_id'] = $accountId;
        $entityData['uname'] = $row['uname'];
        return $entityData;
    }

    /**
     * Handle any conversions from database values to entity values
     *
     * Example of this would be when the database returns a bool, it will be
     * a character 'f' for false or 't' for true. We need to convert that to
     * boolean true or false types for the entity.
     *
     * @param Field $field
     * @param [type] $databaseValue
     * @return mixed
     */
    public function sanitizeDbValuesToEntityFieldValue(Field $field, $databaseValue)
    {
        switch ($field->type) {
            case Field::TYPE_BOOL:
                return ($databaseValue == 't') ? true : false;
            case Field::TYPE_DATE:
            case Field::TYPE_TIMESTAMP:
                return ($databaseValue) ? strtotime($databaseValue) : null;
            case Field::TYPE_OBJECT_MULTI:
                /*
                 * Make sure the id is an actual number
                 * We have to do this because some old entities
                 * have bad values in object_multi fields
                 */
                if ($field->subtype && is_array($databaseValue)) {
                    foreach ($databaseValue as $index => $id) {
                        if (is_numeric($id)) {
                            $databaseValue[$index] = $id;
                        }
                    }
                }

                return $databaseValue;
            default:
                return $databaseValue;
        }
    }

    /**
     * Delete object by id
     *
     * @var EntityInterface $entity The entity to delete
     * @var string $accountId the Account to delete
     * @return bool true on success, false on failure
     */
    protected function deleteHard(EntityInterface $entity, string $accountId): bool
    {
        // Only delete existing objects
        if (!$entity->getEntityId()) {
            return false;
        }

        // Remove revision history
        $this->getDatabase($accountId)->query(
            'DELETE FROM ' . self::ENTITY_REVISION_TABLE .
                ' WHERE entity_id=:entity_id',
            ['entity_id' => $entity->getEntityId()]
        );

        // Delete the object from the object table
        $sql = "DELETE FROM " . self::ENTITY_TABLE .
            " WHERE entity_id=:id AND account_id=:account_id";
        $result = $this->getDatabase($accountId)->query(
            $sql,
            ['id' => $entity->getEntityId(), 'account_id' => $accountId]
        );

        // We just need to make sure the main object was deleted
        return ($result->rowCount() > 0);
    }

    /**
     * Save entity
     *
     * @param EntityInterface $entity The entity to save
     * @return string entity id on success, empty string on failure
     * @throws \RuntimeException If there is a problem saving to the database
     */
    protected function saveData(EntityInterface $entity): string
    {
        $def = $entity->getDefinition();
        $accountId = $entity->getValue('account_id');

        if (empty($accountId)) {
            throw new RuntimeException('account_id must be set for each entity.');
        }

        // Convert to cols=>vals array
        $data = $this->getColumnDataFromEntity($entity, $accountId);

        // Set typei_id to correctly build the sql statement based on custom table definitions
        $data["entity_definition_id"] = $def->getEntityDefinitionId();

        // Set data as JSON (we are replacing columns with this for custom fields)
        $data['field_data'] = json_encode($entity->toArray());

        // Schema version
        $data['schema_version'] = self::SCHEMA_VERSION;

        if ($entity->getValue("revision") > 1) {
            $this->updateEntityData(self::ENTITY_TABLE, $entity, $accountId);
        } else {
            $this->getDatabase($accountId)->insert(self::ENTITY_TABLE, $data);
        }

        return $entity->getEntityId();
    }

    /**
     * Update the entity data
     *
     * @param $targetTable Table that we will be using to update the entity (deleted or active partition)
     * @param $entity The entity that will be updated
     * @param $accountId The account id we are updating for
     */
    private function updateEntityData(string $targetTable, Entity $entity, string $accountId)
    {
        $sql = "UPDATE $targetTable 
                SET field_data = :field_data, f_deleted = :f_deleted, schema_version = :schema_version
                WHERE entity_id=:entity_id AND account_id=:account_id";

        $this->getDatabase($accountId)->query($sql, [
            "field_data" => json_encode($entity->toArray()),
            "f_deleted" => $entity->getValue('f_deleted'),
            "entity_id" => $entity->getValue('entity_id'),
            "account_id" => $accountId,
            "schema_version" => self::SCHEMA_VERSION,
        ]);
    }

    /**
     * Convert fields to column names for saving table and escape for insertion/updates
     *
     * Most of the entity data is put into a jsonb field, so this is basically for
     * special indexed fields like entity_id, and uname that need to exist outside
     * of the jsonb for performance and reference reasons.
     *
     * @param EntityInterface $entity The entity we are saving
     * @param string $accountId
     * @return array("col_name"=>"value")
     */
    private function getColumnDataFromEntity(EntityInterface $entity, string $accountId)
    {
        $ret = [];
        $all_fields = $entity->getDefinition()->getFields();
        // These are the actual columns to enter entity data into
        // from /data/db/*.sql definitions/updates
        $realColumns = [
            'entity_id',
            'account_id',
            'entity_number',
            'uname',
            'ts_entered',
            'ts_updated',
            'f_deleted',
            'commit_id',
            'sort_order',
        ];

        foreach ($all_fields as $fname => $fdef) {
            /*
             * Check if the field name does exists in the object table
             * Most of the entity data are already stored in field_data column
             * So there is no need to build a data array for entity values that
             * don't need to be stored separately from the jsonb field_data
             */
            if (!in_array($fname, $realColumns, true)) {
                continue;
            }

            $val = $entity->getValue($fname);

            switch ($fdef->type) {
                case 'auto':
                    // Calculated fields should not be set from entity
                    break;
                case 'fkey_multi':
                case 'object_multi':
                    $ret[$fname] = json_encode(($val) ? $val : []);
                    break;
                case 'int':
                case 'integer':
                case 'double':
                case 'double precision':
                case 'float':
                case 'real':
                case 'number':
                case 'numeric':
                    if (is_numeric($val)) {
                        if ($fdef->subtype == "integer" && $val) {
                            $ret[$fname] = (int) $val;
                        } else {
                            $ret[$fname] = $val;
                        }
                    } else {
                        $ret[$fname] = null;
                    }

                    break;
                case 'date':
                    // All date fields are epoch timestamps
                    if (is_numeric($val) && $val > 0) {
                        $ret[$fname] = date("Y-m-d", $val);
                    } else {
                        $ret[$fname] = null;
                    }
                    break;
                case 'timestamp':
                    // All timestamp fields are epoch timestamps
                    if (is_numeric($val) && $val > 0) {
                        $ret[$fname] = date(DateTime::ATOM, $val);
                    } else {
                        $ret[$fname] = null;
                    }
                    break;
                case 'text':
                    $tmpval = $val;
                    // Check if the field has a limited length
                    if (is_numeric($fdef->subtype)) {
                        if (strlen($tmpval) > $fdef->subtype) {
                            $tmpval = substr($tmpval, 0, $fdef->subtype);
                        }
                    }
                    $ret[$fname] = $tmpval;
                    break;
                case 'bool':
                    $ret[$fname] = ($val === true);
                    break;
                case 'fkey':
                    // If val is already guid, then this value is already saved in field_data
                    if (Uuid::isValid($val)) {
                        continue 2;
                    }

                    $ret[$fname] = $val ? $val : null;
                    break;
                case 'object':
                    // If val is already guid, then this value is already saved in field_data
                    if (Uuid::isValid($val)) {
                        continue 2;
                    }

                    $ret[$fname] = $val ? $val : null;
                    break;
                default:
                    $ret[$fname] = $val;
                    break;
            }

            // Set fval cache so we do not have to do crazy joins across tables
            if (
                $fdef->type == "fkey" || $fdef->type == "fkey_multi" ||
                $fdef->type == "object" || $fdef->type == "object_multi"
            ) {
                // Get the value names (if set) and save
                $fvals = $entity->getValueNames($fname);
                if (!is_array($fvals)) {
                    $fvals = [];
                }

                $ret[$fname . "_fval"] = json_encode($fvals);
            }
        }

        return $ret;
    }

    /**
     * Serialize data from an array to a string
     *
     * @param array $data
     * @return string
     */
    private function serialize(array $data)
    {
        return json_encode($data);
    }

    /**
     * Check if an entity has moved
     *
     * @param string $entityId The id of the entity that no longer exists - may have moved
     * @param string $accountId The id of the account that owns the entity that potentiall moved
     * @return string New entity id if moved, otherwise empty string
     */
    protected function entityHasMoved(string $entityId, string $accountId): string
    {
        $sql = 'SELECT new_id FROM ' . self::ENTITY_MOVED_TABLE . '  WHERE ' .
            'old_id=:old_id';
        $result = $this->getDatabase($accountId)->query($sql, [
            'old_id' => $entityId
        ]);
        if ($result->rowCount() > 0) {
            $row = $result->fetch();
            return $row['new_id'];
        }

        return false;
    }

    /**
     * Set this object as having been moved to another object
     *
     * @param string $fromId The id to move
     * @param string $toId The unique id of the object this was moved to
     * @param string $accountId The account we are updating
     * @return bool true on succes, false on failure
     */
    public function setEntityMovedTo(string $fromId, string $toId, string $accountId): bool
    {
        if (!$fromId || $fromId == $toId) { // never allow circular reference or blank values
            return false;
        }

        $data = [
            'old_id' => $fromId,
            'new_id' => $toId,
        ];
        $this->getDatabase($accountId)->insert(self::ENTITY_MOVED_TABLE, $data);

        // If it fails an exception will be thrown
        return true;
    }

    /**
     * Save revision snapshot
     *
     * @param Entity $entity The entity to save
     * @return string|bool entity id on success, false on failure
     */
    protected function saveRevision($entity)
    {
        $def = $entity->getDefinition();

        if ($entity->getValue("revision") && $entity->getEntityId() && $def->getEntityDefinitionId()) {
            $insertData = [
                'entity_id' => $entity->getEntityId(),
                'revision' => $entity->getValue("revision"),
                'ts_updated' => 'now',
                'field_data' => json_encode($entity->toArray()),
            ];
            $this->getDatabase($entity->getValue('account_id'))->insert('entity_revision', $insertData);
        }
    }

    /**
     * Get historical values for this entity saved on each revision
     *
     * @param string $entityId
     * @param string $accountId
     * @return array Field data that can be used in EntityInterface::fromArray
     */
    protected function getRevisionsData(string $entityId, string $accountId): array
    {
        if (!$entityId || !$accountId) {
            return [];
        }

        $ret = [];

        $results = $this->getDatabase($accountId)->query(
            'SELECT entity_revision_id, revision, field_data FROM entity_revision WHERE entity_id=:id',
            ['id' => $entityId]
        );
        foreach ($results->fetchAll() as $row) {
            // Load rows and set values in the entity
            $entityData = json_decode($row['field_data'], true);
            $ret[$row['revision']] = $entityData;
        }

        return $ret;
    }

    /**
     * Call used to generate a unique ID for a uname field
     *
     * We use this because a UUID used for entity IDs is not really human-readable
     * so this is a per-account unique ID generator to make it easier for internal
     * users to share entities with numbers.
     *
     * @param string $accountId
     * @return string
     */
    protected function generateUnameId(string $accountId): string
    {
        $result = $this->getDatabase($accountId)->query(
            "SELECT nextval('entity_uname_seq') as num"
        );

        return $result->fetch()['num'];
    }
}
