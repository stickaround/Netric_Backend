<?php

/**
 * This is the base class for all entity indexes
 */

namespace Netric\EntityQuery\Index;

use Netric\EntityDefinition\Field;
use Netric\EntityDefinition\EntityDefinition;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityQuery;
use Netric\EntityQuery\Results;
use Netric\EntityQuery\Plugin\PluginInterface;
use Netric\Entity\Entity;
use Netric\Account\Account;
use Netric\Entity\EntityFactoryFactory;
use Netric\Entity\EntityFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\EntityDefinition\ObjectTypes;
use Ramsey\Uuid\Uuid;

abstract class IndexAbstract
{
    /**
     * Handle to current account
     *
     * @var Account
     */
    protected $account = null;

    /**
     * Entity factory used for instantiating new entities
     *
     * @var EntityFactory
     */
    protected $entityFactory = null;

    /**
     * Index of plugins loaded by objName
     *
     * @var array('obj_name'=>PluginInterface)
     */
    private $pluginsLoaded = [];

    /**
     * Setup this index for the given account
     *
     * @param Account $account
     */
    public function __construct(Account $account)
    {
        $this->account = $account;
        $this->entityFactory = $account->getServiceManager()->get(EntityFactoryFactory::class);

        // Setup the index
        $this->setUp($account);
    }

    /**
     * Setup this index for the given account
     *
     * @param Account $account
     */
    abstract protected function setUp(Account $account);

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
    public function getDefinition($objType)
    {
        $defLoader = $this->account->getServiceManager()->get(EntityDefinitionLoaderFactory::class);
        return $defLoader->get($objType);
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
     * @param string $table The table to query
     * @param string $parent_field The field containing the id of the parent entry
     * @param int $this_id The id of the child element
     */
    public function getHeiarchyUpObj($objType, $oid)
    {
        $ret = [$oid];

        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $ent = $entityLoader->getEntityById($oid, $this->account->getAccountId());
        $ret[] = $ent->getEntityId();
        if ($ent->getDefinition()->parentField) {
            // Make sure parent is set, is of type object, and the object type has not crossed over (could be bad)
            $field = $ent->getDefinition()->getField($ent->getDefinition()->parentField);
            if ($ent->getValue($field->name) && $field->type == FIELD::TYPE_OBJECT && $field->subtype == $objType) {
                $children = $this->getHeiarchyUpObj($field->subtype, $ent->getValue($field->name));
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
     * @param string $table The table to query
     * @param string $parent_field The field containing the id of the parent entry
     * @param int $this_id The id of the child element
     * @param int[] $aProtectCircular Hold array of already referenced objects to chk for array
     */
    public function getHeiarchyDownObj($objType, $entityGuid, $aProtectCircular = [])
    {
        // Check for circular refrences
        if (in_array($entityGuid, $aProtectCircular)) {
            throw new \Exception("Circular reference found in $entityGuid");
        }

        $ret = [$entityGuid];
        $aProtectCircular[] = $entityGuid;

        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $ent = $entityLoader->getEntityById($entityGuid, $this->account->getAccountId());

        if ($ent->getDefinition()->parentField) {
            // Make sure parent is set, is of type object, and the object type has not crossed over (could be bad)
            $field = $ent->getDefinition()->getField($ent->getDefinition()->parentField);
            if ($field->type == FIELD::TYPE_OBJECT && $field->subtype == $objType) {
                $index = $this->account->getServiceManager()->get(IndexFactory::class);
                $query = new EntityQuery($field->subtype);
                $query->where($ent->getDefinition()->parentField)->equals($ent->getEntityId());
                $res = $index->executeQuery($query);
                for ($i = 0; $i < $res->getTotalNum(); $i++) {
                    $subEnt = $res->getEntity($i);
                    $children = $this->getHeiarchyDownObj($objType, $subEnt->getEntityId(), $aProtectCircular);
                    if (count($children)) {
                        $ret = array_merge($ret, $children);
                    }
                }
            }
        }

        return $ret;
    }

    /**
     * Sanitize condition values for querying
     *
     * This function also takes care of translating environment varials such as
     * current user and current user's team into IDs for the query.
     *
     * @param Field $field
     * @param mixed $value
     */
    public function sanitizeWhereCondition(Field $field, $value)
    {
        $user = $this->account->getUser();

        // Cleanup bool
        if ($field->type == Field::TYPE_BOOL && is_string($value)) {
            switch ($value) {
                case 'yes':
                case 'true':
                case 't':
                case '1':
                    return true;
                default:
                    return false;
            }
        }

        // Cleanup dates and times
        if (($field->type == Field::TYPE_DATE || $field->type == Field::TYPE_TIMESTAMP)) {
            // Convert \DateTime to a timestamp
            if ($value instanceof \DateTime) {
                $value = $value->format("Y-m-d h:i:s A e");
            }
            /*
             * The below is causing things fail due to complex queries like
             * monthIsEqual and dayIsEqual. Probably needs some more thought.
            else if (is_numeric($value) && !is_string($value)) {
                $value = date("Y-m-d h:i:s A e", $value);
            }
             */
        }

        // Replace user vars
        if ($user) {
            // Replace current user
            if ($value == UserEntity::USER_CURRENT && $this->fieldContainsUserValues($field)) {
                return $user->getEntityId();
            }

            /*
             * TODO: Handle the below conditions
             *
            // Replace dereferenced current user team
            if ($field->type == "object" && $field->subtype == "user" && $ref_field == "team_id"
                && ($value==USER_CURRENT || $value==TEAM_CURRENTUSER)  && $user->teamId)
                $value = $user->teamId;

            // Replace current user team
            if ($field->type == "fkey" && $field->subtype == "user_teams"
                && ($value==USER_CURRENT || $value==TEAM_CURRENTUSER) && $user->teamId)
                $value = $user->teamId;
            */

            // Replace object reference with user variables
            if (($field->type == Field::TYPE_OBJECT || $field->type == Field::TYPE_OBJECT_MULTI) && !$field->subtype
                && $value == "user:" . UserEntity::USER_CURRENT
            ) {
                return $user->getEntityId();
            }
        }

        /*
        // TODO: Replace grouping labels with id
        if ($field->type == Field::TYPE_GROUPING || $field->type == Field::TYPE_GROUPING_MULTI) {

        }
        if (($field->type == "fkey" || $field->type == "fkey_multi") && $value && !is_numeric($value))
        {
            $grp = $this->obj->getGroupingEntryByName($fieldParts[0], $value);
            if ($grp)
                $value = $grp['id'];
            else
                return;
        }
         */

        return $value;
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
            $plugin->onBeforeExecuteQuery($this->account->getServiceManager(), $query);
        }

        // Recurrence plugin
        $recurrencePlugin = $this->getPlugin("Recurrence");
        $recurrencePlugin->onBeforeExecuteQuery($this->account->getServiceManager(), $query);
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
            $plugin->onAfterExecuteQuery($this->account->getServiceManager(), $query);
        }

        // Recurrence plugin
        $recurrencePlugin = $this->getPlugin("Recurrence");
        $recurrencePlugin->onAfterExecuteQuery($this->account->getServiceManager(), $query);
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
            throw new \RuntimeException("Could not get field {$parts[0]}");
        }

        return $field;
    }
}
