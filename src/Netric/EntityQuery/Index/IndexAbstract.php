<?php

/**
 * This is the base class for all entity indexes
 */

namespace Netric\EntityQuery\Index;

use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\EntityDefinition\Field;
use Netric\EntityDefinition\EntityDefinition;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityQuery\EntityQuery;
use Netric\EntityQuery\Results;
use Netric\EntityQuery\Plugin\PluginInterface;
use Netric\Entity\Entity;
use Netric\Account\Account;
use Netric\Entity\EntityFactory;
use Netric\Entity\EntityLoader;
use Netric\EntityDefinition\EntityDefinitionLoader;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Entity\EntityValueSanitizer;
use Netric\Db\Relational\RelationalDbContainerInterface;
use Netric\Db\Relational\RelationalDbContainer;
use Ramsey\Uuid\Uuid;

abstract class IndexAbstract
{
    /**
     * Handles the creating of new entities
     *
     * @var EntityFactory
     */
    protected $entityFactory = null;

    /**
     * Handles the loading of entity definition
     *
     * @var EntityDefinitionLoader
     */
    protected $entityDefinitionLoader = null;

    /**
     * Handles the loading of existing entities
     *
     * @var EntityLoader
     */
    protected $entityLoader = null;

    /**
     * A service manager that will be used when executing the entity query plugin
     *
     * @var ServiceContainerInterface
     */
    protected $serviceManagerForPlugin = null;

    /**
     * Index of plugins loaded by objName
     *
     * @var array('obj_name'=>PluginInterface)
     */
    private $pluginsLoaded = [];

    /**
     * Setup this index for the given account
     *
     * @param RelationalDbContainer $database Handles the database actions
     * @param EntityFactory $entityFactory Handles the creating of new entities
     * @param EntityDefinitionLoader $defLoader Handles the loading of entity definition
     * @param EntityLoader $entityLoader Handles the loading of existing entities
     * @param EntityValueSanitizer $entityValueSanitizer Handles the sanitizing of condition values in the query
     * @param ServiceContainerInterface $serviceManagerForPlugin A service manager that will be used when executing the entity query plugin
     */
    public function __construct(
        RelationalDbContainer $dbContainer,
        EntityFactory $entityFactory,
        EntityDefinitionLoader $entityDefinitionLoader,
        EntityLoader $entityLoader,
        EntityValueSanitizer $entityValueSanitizer,
        ServiceContainerInterface $serviceManagerForPlugin
    ) {
        $this->entityFactory = $entityFactory;
        $this->entityDefinitionLoader = $entityDefinitionLoader;
        $this->entityLoader = $entityLoader;
        $this->serviceManagerForPlugin = $serviceManagerForPlugin;

        // Setup the entity query index
        $this->setUp($dbContainer, $entityValueSanitizer);
    }

    /**
     * Save an object to the index
     *
     * @param Entity $entity Entity to save
     * @return bool true on success, false on failure
     */
    abstract public function save(Entity $entity);

    /**
     * Delete an object from the index
     *
     * @param string $id Unique id of object to delete
     * @return bool true on success, false on failure
     */
    abstract public function delete($id);

    /**
     * Execute a query and return the results
     *
     * @param EntityQuery &$query The query to execute
     * @param Results $results Optional results set to use. Otherwise create new.
     * @return Results
     */
    abstract protected function queryIndex(EntityQuery $query, Results $results = null);

    /**
     * Execute a query and return the results
     *
     * @param EntityQuery $query A query to execute
     * @param Results $results Optional results set to use. Otherwise create new.
     * @return Results
     */
    public function executeQuery(EntityQuery $query, Results $results = null)
    {
        // Trigger any plugins for before the query completed
        $this->beforeExecuteQuery($query);

        // Get results form the index for a query
        $ret = $this->queryIndex($query, $results);

        // Trigger any plugins after the query completed
        $this->afterExecuteQuery($query);

        return $ret;
    }

    /**
     * Split a full text string into an array of terms
     *
     * @param string $qstring The entered text
     * @return array Array of terms
     */
    public function queryStringToTerms($qstring)
    {
        if (!$qstring) {
            return [];
        }

        $res = [];
        //preg_match_all('/(?<!")\b\w+\b|\@(?<=")\b[^"]+/', $qstr, $res, PREG_PATTERN_ORDER);
        preg_match_all('~(?|"([^"]+)"|(\S+))~', $qstring, $res);
        return $res[0]; // not sure why but for some reason results are in a multi-dimen array, we just need the first
    }

    /**
     * Get a definition by name
     *
     * @param string $objType
     */
    public function getDefinition(string $objType, string $accountId)
    {
        return $this->entityDefinitionLoader->get($objType, $accountId);
    }

    /**
     * Get ids of all parent ids in a parent-child relationship
     *
     * @param string $table The table to query
     * @param string $parent_field The field containing the id of the parent entry
     * @param int $this_id The id of the child element
     */
    public function getHeiarchyUp(Field $field, $this_id)
    {
        $parent_arr = [$this_id];

        // TODO: finish
        /*
        if ($this_id && $parent_field)
        {
            $query = "select $parent_field as pid from $table where id='$this_id'";
            $result = $dbh->Query($query);
            if ($dbh->GetNumberRows($result))
            {
                $row = $dbh->GetNextRow($result, 0);

                $subchildren = $this->getHeiarchyUp($table, $parent_field, $row['pid']);

                if (count($subchildren))
                    $parent_arr = array_merge($parent_arr, $subchildren);
            }
            $dbh->FreeResults($result);
        }
         */

        return $parent_arr;
    }

    /**
     * Get ids of all parent entries in a parent-child relationship of an object
     *
     * @param string $objType The object type of the entity
     * @param string $entityGuid The id of the entity
     * @param string $accountId The account we are going to query entities for
     */
    public function getHeiarchyUpObj($objType, $entityGuid, $accountId)
    {
        $ret = [$entityGuid];

        $ent = $this->entityLoader->getEntityById($entityGuid, $accountId);
        $ret[] = $ent->getEntityId();
        if ($ent->getDefinition()->parentField) {
            // Make sure parent is set, is of type object, and the object type has not crossed over (could be bad)
            $field = $ent->getDefinition()->getField($ent->getDefinition()->parentField);
            if ($ent->getValue($field->name) && $field->type == FIELD::TYPE_OBJECT && $field->subtype == $objType) {
                $children = $this->getHeiarchyUpObj($field->subtype, $ent->getValue($field->name), $accountId);
                if (count($children)) {
                    $ret = array_merge($ret, $children);
                }
            }
        }

        return $ret;
    }

    /**
     * Get ids of all child entries in a parent-child relationship of an object
     *
     * @param string $objType The object type of the entity
     * @param string $entityGuid The id of the entity
     * @param string $accountId The account we are going to query entities for
     * @param int[] $aProtectCircular Hold array of already referenced objects to chk for array
     */
    public function getHeiarchyDownObj($objType, $entityId, $accountId, $aProtectCircular = [])
    {
        // Check for circular refrences
        if (in_array($entityId, $aProtectCircular)) {
            throw new \Exception("Circular reference found in $entityId");
        }

        $ret = [$entityId];
        $aProtectCircular[] = $entityId;

        $ent = $this->entityLoader->getEntityById($entityId, $accountId);

        if ($ent->getDefinition()->parentField) {
            // Make sure parent is set, is of type object, and the object type has not crossed over (could be bad)
            $field = $ent->getDefinition()->getField($ent->getDefinition()->parentField);
            if ($field->type == FIELD::TYPE_OBJECT && $field->subtype == $objType) {
                $query = new EntityQuery($field->subtype, $accountId);
                $query->where($ent->getDefinition()->parentField)->equals($ent->getEntityId());
                $res = $this->executeQuery($query);
                for ($i = 0; $i < $res->getTotalNum(); $i++) {
                    $subEnt = $res->getEntity($i);
                    $children = $this->getHeiarchyDownObj($objType, $subEnt->getEntityId(), $accountId, $aProtectCircular);
                    if (count($children)) {
                        $ret = array_merge($ret, $children);
                    }
                }
            }
        }

        return $ret;
    }

    /**
     * Evaluate if the field can contain user values
     *
     * @param Field $field The field that we will be evaluating
     * @return True if the field can contain user values, False if not
     */
    private function fieldContainsUserValues(Field $field)
    {
        // If field subtype is not a user, then we do not need to proceed
        if ($field->subtype !== ObjectTypes::USER) {
            return false;
        }

        switch ($field->type) {
            case Field::TYPE_OBJECT:
            case Field::TYPE_OBJECT_MULTI:
                return true;
                break;
        }

        return false;
    }

    /**
     * Check to see if we have any plugins listening before the query executes
     *
     * @param EntityQuery $query The query that is about to run
     */
    private function beforeExecuteQuery(EntityQuery $query)
    {
        $plugin = $this->getPlugin($query->getObjType());
        if ($plugin) {
            $plugin->onBeforeExecuteQuery($this->serviceManagerForPlugin, $query);
        }

        // Recurrence plugin
        $recurrencePlugin = $this->getPlugin("Recurrence");
        $recurrencePlugin->onBeforeExecuteQuery($this->serviceManagerForPlugin, $query);
    }

    /**
     * Check to see if we have any plugins listening after the query executes
     *
     * @param EntityQuery $query The query that just ran
     */
    private function afterExecuteQuery(EntityQuery $query)
    {
        $plugin = $this->getPlugin($query->getObjType());
        if ($plugin) {
            $plugin->onAfterExecuteQuery($this->serviceManagerForPlugin, $query);
        }

        // Recurrence plugin
        $recurrencePlugin = $this->getPlugin("Recurrence");
        $recurrencePlugin->onAfterExecuteQuery($this->serviceManagerForPlugin, $query);
    }

    /**
     * Look for and constuct a query plugin if it exists
     *
     * @param string $objType The object type name
     * @return PluginInterface|null
     */
    private function getPlugin($objType)
    {
        $plugin = null;

        // Check if we have already loaded this plugin
        if (isset($this->pluginsLoaded[$objType])) {
            return $this->pluginsLoaded[$objType];
        }

        $objClassName = str_replace("_", " ", $objType);
        $objClassName = ucwords($objClassName);
        $objClassName = str_replace(" ", "", $objClassName);

        $pluginName = "\\Netric\\EntityQuery\\Plugin\\" . $objClassName . 'QueryPlugin';
        if (class_exists($pluginName)) {
            // Construct a new plugin
            $plugin = new $pluginName();

            // Cache for future calls
            $this->pluginsLoaded[$objType] = $plugin;
        }

        return $plugin;
    }

    /**
     * Function that will get a Field Definition using a field name
     *
     * @param EntityDefinition $entityDefinition Definition for the entity being queried
     * @param String $fieldName The name of the field that we will be using to get a Field Definition
     *
     * @return Field
     */
    public function getFieldUsingFieldName(EntityDefinition $entityDefinition, $fieldName)
    {
        // Look for associated object conditions
        $parts = [$fieldName];
        $refField = "";

        if (strpos($fieldName, ".")) {
            $parts = explode(".", $fieldName);

            if (count($parts) > 1) {
                $fieldName = $parts[0];
                $refField = $parts[1];
                $field->type = "object_dereference";
            }
        }

        // Get the field
        $field = $entityDefinition->getField($parts[0]);

        // If we do not have a field then throw an exception
        if (!$field) {
            throw new \RuntimeException("Could not get field {$parts[0]} of " . $entityDefinition->getObjType());
        }

        return $field;
    }
}
