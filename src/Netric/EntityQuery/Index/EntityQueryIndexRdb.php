<?php

namespace Netric\EntityQuery\Index;

use Netric\EntityDefinition\Field;
use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityQuery;
use Netric\EntityQuery\Where;
use Netric\EntityQuery\Results;
use Netric\EntityQuery\Aggregation;
use Netric\Account\Account;
use Netric\Entity\Entity;
use Netric\EntityQuery\Aggregation\AggregationInterface;
use Netric\Db\Relational\RelationalDbInterface;
use Netric\Db\Relational\RelationalDbFactory;

/**
 * Relational Database implementation of indexer for querying objects
 */
class EntityQueryIndexRdb extends IndexAbstract implements IndexInterface
{
    /**
     * Handle to database
     *
     * @var RelationalDbInterface
     */
    private $database = null;

    /**
     * Setup this index for the given account
     *
     * @param Account $account
     */
    protected function setUp(Account $account)
    {
        $this->database = $account->getServiceManager()->get(RelationalDbFactory::class);
    }

    /**
     * Save an object to the index
     *
     * @param Entity $entity Entity to save
     * @return bool true on success, false on failure
     */
    public function save(Entity $entity)
    {
        $def = $entity->getDefinition();

        $tableName = $def->getTable();
        $tableName .= ($entity->isDeleted()) ? "_del" : "_act";

        // Get indexed text
        $fields = $def->getFields();
        $fieldTextValues = [];
        foreach ($fields as $field) {
            if ($field->type != FIELD::TYPE_GROUPING_MULTI && $field->type != FIELD::TYPE_OBJECT_MULTI) {
                $fieldTextValues[] = strtolower(strip_tags($entity->getValue($field->name)));
            }
        }

        $sql = "UPDATE $tableName
                SET tsv_fulltext=to_tsvector('english', :full_text_terms)
                WHERE id=:id";

        /*
         * We will be using rdb::query() here instead of rdb::update()
         * since we are using to_vector() pgsql function and not updating a field using a normal data
         */
        $queryParams = ["id" => $entity->getId(), "full_text_terms" => implode(" ", $fieldTextValues)];
        $result = $this->database->query($sql, $queryParams);

        return $result->rowCount() > 0;
    }

    /**
     * Delete an object from the index
     *
     * @param string $objectId Unique id of object to delete
     * @return bool true on success, false on failure
     */
    public function delete($objectId)
    {
        // Nothing need be done because this index queries the source persistent store
        return true;
    }

    /**
     * Execute a query and return the results
     *
     * @param EntityQuery $query The query to execute
     * @param Results $results Optional results set to use. Otherwise create new.
     * @return Results
     */
    protected function queryIndex(EntityQuery $query, Results $results = null)
    {
        // If re-running an existing result (getting the next page) then clear
        if ($results !== null) {
            $results->clearEntities();
        }

        // Create results object if it does not exist
        if ($results === null) {
            $results = new Results($query, $this);
        }

        // Make sure that we have an entity definition before executing a query
        $entityDefinition = $this->getDefinition($query->getObjType());

        // Should never happen, but just in case if we do not have an entity definition throw an exception
        if (!$entityDefinition) {
            throw new \RuntimeException(
                "No entity definition" . var_export($query->toArray(), true)
            );
        }

        // Get table to query
        $objectTable = $entityDefinition->getTable();

        // Start building the condition string
        $conditionString = "";
        $queryConditions = $query->getWheres();

        // Flag to indicate if we need to close an opening ( in a query
        $parenShouldBeClosed = false;

        // Loop thru the query conditions and check for special fields
        foreach ($queryConditions as $condition) {
            $whereString = $this->buildConditionStringAndSetParams($entityDefinition, $condition);

            // Make sure that we have built an advanced condition string
            if (!empty($whereString)) {
                // Wrap all AND queries in () to make order of operations clear when OR is encoutered
                if ($condition->bLogic == Where::COMBINED_BY_AND) {
                    if ($conditionString) {
                        $conditionString .= ") AND (";
                    } elseif (empty($conditionString)) {
                        $conditionString .= " ( ";
                    }
                    $parenShouldBeClosed = true;
                } elseif ($condition->bLogic == Where::COMBINED_BY_OR) {
                    $conditionString .= ($conditionString) ? " OR " : " (";
                }

                $conditionString .= $whereString;
            }
        }

        // Close any dangling opening (
        if ($conditionString) {
            $conditionString .= ")";
        }

        /*
         * If there is no f_deleted field condition set and entityDefinition has f_deleted field
         * We will make sure that we will get the non-deleted records
         */
        if (!$query->fieldIsInWheres('f_deleted') && $entityDefinition->getField("f_deleted")) {
            // If $conditionString is not empty, then we will just append the "and" blogic
            if (!empty($conditionString)) {
                $conditionString .= " and ";
            }

            $conditionString .= "f_deleted=false";
        }

        // Get order by from $query and setup the sort order
        $sortOrder = [];
        if (count($query->getOrderBy())) {
            $orderBy = $query->getOrderBy();

            foreach ($orderBy as $sort) {
                $sortOrder[] = "{$sort->fieldName} $sort->direction";
            }
        }

        // Start constructing query
        $sql = "SELECT * FROM $objectTable";

        // Set the query condition string if it is available
        if (!empty($conditionString)) {
            $sql .= " WHERE $conditionString";
        }

        // Check if we have order by string
        if (count($sortOrder)) {
            $sql .= " ORDER BY " . implode(", ", $sortOrder);
        }

        // Check if we need to add limit
        if (!empty($query->getLimit())) {
            $sql .= " LIMIT {$query->getLimit()}";
        }

        // Check if we need to add offset
        if (!empty($query->getOffset())) {
            $sql .= " OFFSET {$query->getOffset()}";
        }

        $result = $this->database->query($sql);

        // Process the raw data of entities and update the $results
        $this->processEntitiesRawData($entityDefinition, $result->fetchAll(), $results);

        // Set the total num of the Results
        $this->setResultsTotalNum($entityDefinition, $results, $conditionString);

        // Get the aggregations and update the Results' aggregations
        if ($query->hasAggregations()) {
            $aggregations = $query->getAggregations();
            foreach ($aggregations as $agg) {
                $this->queryAggregation($entityDefinition, $agg, $results, $conditionString);
            }
        }

        return $results;
    }

    /**
     * Function that will set the total num for results
     *
     * @param EntityDefinition $entityDefinition Definition for the entity being queried
     * @param Results $results The results that we will be updating its total num
     * @param string $conditionString The query condition that will be used for filtering
     */
    private function setResultsTotalNum(EntityDefinition $entityDefinition, Results $results, $conditionString)
    {
        // Get table to query
        $objectTable = $entityDefinition->getTable();

        // Create the sql string to get the total num
        $sql = "SELECT count(*) as total_num FROM $objectTable";

        // Set the query condition string here if it is available
        if (!empty($conditionString)) {
            $sql .= " WHERE $conditionString";
        }

        $result = $this->database->query($sql);
        if ($result->rowCount()) {
            $row = $result->fetch();
            $results->setTotalNum($row["total_num"]);
        }
    }

    /**
     * Process the raw data of entities and add them in the $results
     *
     * @param EntityDefinition $entityDefinition Definition for the entity being queried
     * @param Array $entitiesRawDataArray An array of entities raw data that will be processed
     * @param Results $results Results that will be used where we will add the processed entities
     */
    private function processEntitiesRawData(EntityDefinition $entityDefinition, array $entitiesRawDataArray, Results $results)
    {
        // Get fields for this object type (used in decoding multi-valued fields)
        $ofields = $entityDefinition->getFields();

        foreach ($entitiesRawDataArray as $entityData) {
            // Decode multival fields into arrays of values
            foreach ($ofields as $fname => $fdef) {
                if ($fdef->type == FIELD::TYPE_GROUPING_MULTI || $fdef->type == FIELD::TYPE_OBJECT_MULTI) {
                    if (isset($entityData[$fname])) {
                        $dec = json_decode($entityData[$fname], true);
                        if ($dec !== false) {
                            $entityData[$fname] = $dec;
                        }
                    }
                }

                if (
                    $fdef->type == FIELD::TYPE_GROUPING || $fdef->type == FIELD::TYPE_OBJECT
                    || $fdef->type == FIELD::TYPE_GROUPING_MULTI || $fdef->type == FIELD::TYPE_OBJECT_MULTI
                ) {
                    if (isset($entityData[$fname . "_fval"])) {
                        $dec = json_decode($entityData[$fname . "_fval"], true);
                        if ($dec !== false) {
                            $entityData[$fname . "_fval"] = $dec;
                        }
                    }
                }
            }

            // Set and add entity
            $entity = $this->entityFactory->create($entityDefinition->getObjType());
            $entity->fromArray($entityData);
            $entity->resetIsDirty();
            $results->addEntity($entity);
        }
    }

    /**
     * Build the conditions string using the $condition argument provided
     *
     * @param EntityDefinition $entityDefinition Definition for the entity being queried
     * @param Array $condition The where condition that we are dealing with
     * @return string Query condition string with param values pre-populated and quoted
     */
    public function buildConditionStringAndSetParams(EntityDefinition $enityDefinition, $condition): string
    {
        $fieldName = $condition->fieldName;
        $operator = $condition->operator;

        // If we have a full text condition, then return a vector search
        if ($fieldName === "*") {
            return "(tsv_fulltext @@ plainto_tsquery(" . $this->database->quote($condition->value) . "))";
        }

        // Should never happen, but just in case if operator is missing throw an exception
        if (!$operator) {
            throw new \RuntimeException("No operator provided for " . var_export($condition, true));
        }

        // Get the Field Definition using the field name provided in the $condition
        $field = $this->getFieldUsingFieldName($enityDefinition, $fieldName);

        // Sanitize and replace environment variables like 'current_user' to concrete vals
        $condition->value = $this->sanitizeWhereCondition($field, $condition->value);

        // After sanitizing the condition value, then we are now ready to build the condition string
        $value = $condition->value;

        $conditionString = "";
        switch ($operator) {
            case Where::OPERATOR_EQUAL_TO:
                $conditionString = $this->buildIsEqual($enityDefinition, $field, $condition);
                break;
            case Where::OPERATOR_NOT_EQUAL_TO:
                $conditionString = $this->buildIsNotEqual($enityDefinition, $field, $condition);
                break;
            case Where::OPERATOR_GREATER_THAN:
                switch ($field->type) {
                    case FIELD::TYPE_OBJECT_MULTI:
                    case FIELD::TYPE_OBJECT:
                    case FIELD::TYPE_GROUPING_MULTI:
                    case FIELD::TYPE_TEXT:
                        break;
                    default:
                        if ($field->type == FIELD::TYPE_TIMESTAMP) {
                            $value = (is_numeric($value)) ? date("Y-m-d H:i:s T", $value) : $value;
                        } elseif ($field->type == FIELD::TYPE_DATE) {
                            $value = (is_numeric($value)) ? date("Y-m-d", $value) : $value;
                        }

                        $conditionString = "$fieldName>" . $this->database->quote($value);
                        break;
                }
                break;
            case Where::OPERATOR_LESS_THAN:
                switch ($field->type) {
                    case FIELD::TYPE_OBJECT_MULTI:
                    case FIELD::TYPE_OBJECT:
                    case FIELD::TYPE_GROUPING_MULTI:
                    case FIELD::TYPE_TEXT:
                        break;
                    default:
                        if ($field->type == FIELD::TYPE_TIMESTAMP) {
                            $value = (is_numeric($value)) ? date("Y-m-d H:i:s T", $value) : $value;
                        } elseif ($field->type == FIELD::TYPE_DATE) {
                            $value = (is_numeric($value)) ? date("Y-m-d", $value) : $value;
                        }

                        $conditionString = "$fieldName<" . $this->database->quote($value);
                        break;
                }
                break;
            case Where::OPERATOR_GREATER_THAN_OR_EQUAL_TO:
                switch ($field->type) {
                    case FIELD::TYPE_OBJECT:
                        if ($field->subtype) {
                            $children = $this->getHeiarchyDownObj($field->subtype, $value);

                            foreach ($children as $child) {
                                $multiCond[] = "$fieldName=" . $this->database->quote($child);
                            }

                            $conditionString = "(" . implode(" or ", $multiCond) . ")";
                            break;
                        }
                        break;
                    case FIELD::TYPE_OBJECT_MULTI:
                    case FIELD::TYPE_GROUPING_MULTI:
                    case FIELD::TYPE_TEXT:
                        break;
                    default:
                        if ($field->type == FIELD::TYPE_TIMESTAMP) {
                            $value = (is_numeric($value)) ? date("Y-m-d H:i:s T", $value) : $value;
                        } elseif ($field->type == FIELD::TYPE_DATE) {
                            $value = (is_numeric($value)) ? date("Y-m-d", $value) : $value;
                        }

                        $conditionString = "$fieldName>=" . $this->database->quote($value);
                        break;
                }
                break;
            case Where::OPERATOR_LESS_THAN_OR_EQUAL_TO:
                switch ($field->type) {
                    case FIELD::TYPE_OBJECT:
                        if (
                            !empty($field->subtype)
                            && $entityDefinition->parentField == $fieldName
                            && is_numeric($value)
                        ) {
                            $refDef = $this->getDefinition($field->subtype);
                            $refDefTable = $refDef->getTable(true);

                            if ($refDef->parentField) {
                                $conditionString = "$fieldName in (WITH RECURSIVE children AS
												(
													-- non-recursive term
                                                    SELECT id FROM $refDefTable 
                                                    WHERE id=" . $this->database->quote($value) . "
													UNION ALL
													-- recursive term
													SELECT $refDefTable.id
													FROM $refDefTable
													JOIN children AS chld
														ON ($refDefTable.{$refDef->parentField}=chld.id)
												)
												SELECT id
												FROM children)";
                            }
                        }
                        break;
                    case FIELD::TYPE_OBJECT_MULTI:
                    case FIELD::TYPE_GROUPING_MULTI:
                    case FIELD::TYPE_TEXT:
                        break;
                    default:
                        if ($field->type == FIELD::TYPE_TIMESTAMP) {
                            $value = (is_numeric($value)) ? date("Y-m-d H:i:s T", $value) : $value;
                        } elseif ($field->type == FIELD::TYPE_DATE) {
                            $value = (is_numeric($value)) ? date("Y-m-d", $value) : $value;
                        }

                        $conditionString = "$fieldName<=" . $this->database->quote($value);
                        break;
                }
                break;
            case Where::OPERATOR_BEGINS:
            case Where::OPERATOR_BEGINS_WITH:
                switch ($field->type) {
                    case FIELD::TYPE_TEXT:
                        if ($field->subtype) {
                            $conditionString = "lower($fieldName) like " . $this->database->quote(strtolower("$value%"));
                        } else {
                            $conditionString = "to_tsvector($fieldName) @@ 
                                plainto_tsquery(" . $this->database->quote("$value*") . ")";
                        }
                        break;
                }
                break;
            case Where::OPERATOR_CONTAINS:
                switch ($field->type) {
                    case FIELD::TYPE_TEXT:
                        if ($field->subtype) {
                            $conditionString = "lower($fieldName) like " . $this->database->quote(strtolower("%$value%"));
                        } else {
                            $conditionString = "to_tsvector($fieldName) @@ plainto_tsquery(" . $this->database->quote($value) . ")";
                        }

                        break;
                    default:
                        break;
                }
                break;
        }

        // If we are dealing with date operators
        if (($field->type == FIELD::TYPE_DATE || $field->type == FIELD::TYPE_TIMESTAMP)
            && empty($conditionString)
        ) {
            $conditionString = $this->buildConditionWithDateOperators($condition);
        }

        return $conditionString;
    }

    /**
     * Function that will get a Field Definition using a field name
     *
     * @param EntityDefinition $entityDefinition Definition for the entity being queried
     * @param String $fieldName The name of the field that we will be using to get a Field Definition
     *
     * @return Field
     */
    private function getFieldUsingFieldName(EntityDefinition $entityDefinition, $fieldName)
    {
        // Look for associated object conditions
        $parts = array($fieldName);
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

    /**
     * Function that will build the conditions with date operators
     *
     * @param Array $condition The where condition that we are dealing with
     * @return string
     */
    private function buildConditionWithDateOperators($condition)
    {
        $conditionString = "";
        $fieldName = $condition->fieldName;
        $value = $condition->value;
        $dateType = $condition->getOperatorDateType();

        switch ($condition->operator) {
                // Operator Date is equal
            case Where::OPERATOR_DAY_IS_EQUAL:
            case Where::OPERATOR_MONTH_IS_EQUAL:
            case Where::OPERATOR_YEAR_IS_EQUAL:
                // If the value is trying to get the current date
                if ($value === "<%current_$dateType%>") {
                    $conditionString = "extract($dateType from $fieldName)=extract('$dateType' from now())";
                } else {
                    $conditionString = "extract($dateType from $fieldName)=" . $this->database->quote($value);
                }
                break;

                // Operator Last X DateType
            case Where::OPERATOR_LAST_X_DAYS:
            case Where::OPERATOR_LAST_X_WEEKS:
            case Where::OPERATOR_LAST_X_MONTHS:
            case Where::OPERATOR_LAST_X_YEARS:
                $conditionString = "$fieldName>=(now()-INTERVAL '$value {$dateType}s')";
                break;

                // Operator Next DateType
            case Where::OPERATOR_NEXT_X_DAYS:
            case Where::OPERATOR_NEXT_X_WEEKS:
            case Where::OPERATOR_NEXT_X_MONTHS:
            case Where::OPERATOR_NEXT_X_YEARS:
                $conditionString = "$fieldName>=now() and $fieldName<=(now()+INTERVAL '$value {$dateType}s')";
                break;
        }

        return $conditionString;
    }

    /**
     * Add conditions for "is_eqaul" operator
     *
     * @param EntityDefinition $entityDefinition Definition for the entity being queried
     * @param string $field The current field that we will handle to build the is_equal where condition
     * @param Array $condition The where condition that we are dealing with
     */
    private function buildIsEqual(EntityDefinition $entityDefinition, $field, $condition)
    {
        $objectTable = $entityDefinition->getTable();
        $fieldName = $condition->fieldName;
        $value = $condition->value;

        $conditionString = "";
        switch ($field->type) {
            case FIELD::TYPE_OBJECT:
                if ($value) {
                    $conditionString = "$fieldName=" . $this->database->quote($value);
                    //$conditionString = "field_data->>'$fieldName'='$value'";
                } else {
                    $conditionString = "(field_data->>'$fieldName') is null";

                    // if (empty($field->subtype)) {
                    //     $conditionString .= " or field_data->'$fieldName'=''";
                    // }
                }
                break;
            case FIELD::TYPE_OBJECT_MULTI:
                $conditionString = $this->buildObjectMultiQueryCondition($entityDefinition, $field, $condition);
                break;
            case 'object_dereference':
                // TODO: Ask sky about what is object_dereference
                if ($field->subtype && isset($refField)) {
                    // Create subquery
                    /*$subQuery = new EntityQuery($field->subtype);
                    $subQuery->where($refField)->equals($value);
                    $subIndex = new EntityQueryIndexRdb($this->account);
                    $tmp_obj_cnd_str = $subIndex->buildConditionStringAndSetParams($subQuery);
                    $refDef = $this->getDefinition($field->subtype);

                    if ($value == "" || $value == "NULL") {
                        $buf .= " " . $objectTable . ".$fieldName not in (select id from " . $refDef->getTable() . "
                                                                                where $tmp_obj_cnd_str) ";
                    } else {
                        $buf .= " " . $objectTable . ".$fieldName in (select id from " . $refDef->getTable() . "
                                                                                where $tmp_obj_cnd_str) ";
                    }*/ }
                break;
            case FIELD::TYPE_GROUPING_MULTI:
                $multiCond = [];
                $fkeyTableRef = $field->fkeyTable['ref_table']['ref'];

                // Check if the fkey table has a parent
                if (isset($field->fkeyTable["parent"]) && is_numeric($value)) {
                    $children = $this->getHeiarchyDownGrp($field, $value);

                    // Make sure that we have a children
                    if (!empty($children)) {
                        foreach ($children as $child) {
                            $multiCond[] = "$fkeyTableRef=" . $this->database->quote($child);
                        }
                    } else {
                        $multiCond[] = "$fkeyTableRef=" . $this->database->quote($value);
                    }
                } elseif (!empty($value)) {
                    $multiCond[] = "$fkeyTableRef=" . $this->database->quote($value);
                }

                $thisfld = $field->fkeyTable['ref_table']["this"];
                $reftbl = $field->fkeyTable['ref_table']['table'];

                if (empty($value)) {
                    $conditionString = " NOT EXISTS (select 1 from  $reftbl where $reftbl.$thisfld=$objectTable.id) ";
                } else {
                    $multiCondString = implode(" or ", $multiCond);
                    $conditionString = " EXISTS (select 1 from  $reftbl where $reftbl.$thisfld=$objectTable.id and ($multiCondString)) ";
                }
                break;
            case FIELD::TYPE_GROUPING:
                $conditionString = $this->buildGroupingQueryCondition($entityDefinition, $field, $condition);
                break;
            case FIELD::TYPE_TEXT:
                if (empty($value)) {
                    $conditionString = "($fieldName is null OR $fieldName='')";
                } elseif ($field->subtype) {
                    $conditionString = "lower($fieldName)=" . $this->database->quote(strtolower($value));
                } else {
                    $conditionString = "to_tsvector($fieldName) @@ plainto_tsquery(" . $this->database->quote($value) . ")";
                }
                break;
            case FIELD::TYPE_BOOL:
                $conditionString = "$fieldName=" . $this->database->quote($value);
                break;
            case FIELD::TYPE_DATE:
            case FIELD::TYPE_TIMESTAMP:
                if ($field->type == FIELD::TYPE_TIMESTAMP) {
                    $value = (is_numeric($value)) ? date("Y-m-d H:i:s T", $value) : $value;
                } elseif ($field->type == FIELD::TYPE_DATE) {
                    $value = (is_numeric($value)) ? date("Y-m-d", $value) : $value;
                }
            default:
                if (!empty($value)) {
                    $conditionString = "$fieldName=" . $this->database->quote($value);
                } else {
                    $conditionString = "$fieldName is null";
                }
                break;
        }

        return $conditionString;
    }

    /**
     * Add conditions for "is_not_eqaul" operator
     *
     * @param EntityDefinition $entityDefinition Definition for the entity being queried
     * @param Field $field The current field that we will handle to build the is_not_equal where condition
     * @param Array $condition The where condition that we are dealing with
     */
    private function buildIsNotEqual(EntityDefinition $entityDefinition, $field, $condition)
    {
        $objectTable = $entityDefinition->getTable();
        $fieldName = $condition->fieldName;
        $value = $condition->value;
        $conditionString = "";
        switch ($field->type) {
            case FIELD::TYPE_OBJECT:
                if ($field->subtype) {
                    if (empty($value)) {
                        $conditionString = "$fieldName is not null";
                    } elseif (isset($field->subtype) && $entityDefinition->parentField == $fieldName && $value) {
                        $refDef = $this->getDefinition($field->subtype);
                        $refDefTable = $refDef->getTable(true);
                        $parentField = $refDef->parentField;

                        if ($refDef->parentField) {
                            $conditionString = "$fieldName not in (WITH RECURSIVE children AS
                                    (
                                        -- non-recursive term
                                        SELECT id FROM $refDefTable WHERE id=" . $this->database->quote($value) . "
                                        UNION ALL
                                        -- recursive term
                                        SELECT $refDefTable.id
                                        FROM $refDefTable
                                        JOIN children AS chld
                                            ON ($refDefTable.$parentField = chld.id)
                                    )
                                    SELECT id
                                    FROM children)";
                        }
                    } else {
                        $conditionString = "$fieldName!=" . $this->database->quote($value);
                    }
                }
                break;

            case FIELD::TYPE_OBJECT_MULTI:
                $conditionString = $this->buildObjectMultiQueryCondition($entityDefinition, $field, $condition);
                break;
            case 'object_dereference':
                /*$tmp_cond_str = "";
                if ($field->subtype && $refField) {
                    // Create subquery
                    $subQuery = new \Netric\EntityQuery($field->subtype);
                    $subQuery->where($refField, $operator, $value);
                    $subIndex = new \Netric\EntityQuery\Index\Pgsql($this->account);
                    $tmp_obj_cnd_str = $subIndex->buildConditionStringAndSetParams($subQuery);
                    $refDef = $this->getDefinition($field->subtype);

                    if ($value == "" || $value == "NULL") {
                        $buf .= " " . $objectTable . ".$fieldName is not null ";
                    } else {
                        $buf .= " " . $objectTable . ".$fieldName not in (select id from " . $refDef->getTable(true) . "
                                                                                where $tmp_obj_cnd_str) ";
                    }
                }*/
                break;
            case FIELD::TYPE_GROUPING_MULTI:
                $fkeyRefField = $field->fkeyTable['ref_table']['this'];
                $fkeyRefTable = $field->fkeyTable['ref_table']['table'];
                $fkeyTableRef = $field->fkeyTable['ref_table']['ref'];

                if (empty($value)) {
                    $conditionString = "$objectTable.id in (select $fkeyRefField from $fkeyRefTable)";
                } else {
                    $multiCond = [];

                    // Check first if the fkey table has a parent
                    if (!empty($field->fkeyTable["parent"]) && is_numeric($value)) {
                        $children = $this->getHeiarchyDownGrp($field, $value);

                        // Make sure that we have $children
                        if (!empty($children)) {
                            foreach ($children as $child) {
                                $multiCond[] = "$fkeyTableRef=" . $this->database->quote($child);
                            }
                        } else {
                            $multiCond[] = "$fkeyTableRef=" . $this->database->quote($value);
                        }
                    } else {
                        $multiCond[] = "$fkeyTableRef=" . $this->database->quote($value);
                    }

                    $multiCondString = implode(" or ", $multiCond);
                    $conditionString = "$objectTable.id not in (select $fkeyRefField from $fkeyRefTable where $multiCondString)";
                }

                break;
            case FIELD::TYPE_GROUPING:
                $conditionString = $this->buildGroupingQueryCondition($entityDefinition, $field, $condition);
                break;
            case FIELD::TYPE_TEXT:
                if (empty($value)) {
                    $conditionString = "($fieldName!='' AND $fieldName is not NULL)";
                } elseif ($field->subtype) {
                    $qoutedValueToLower = $this->database->quote(strtolower($value));
                    $conditionString = "lower($fieldName)!=$qoutedValueToLower";
                } else {
                    $quotedValue = $this->database->quote($value);
                    $conditionString = " (to_tsvector($fieldName) @@ plainto_tsquery($quotedValue))='f'";
                }
                break;
            case FIELD::TYPE_BOOL:
                $conditionString = "$fieldName!=" . $this->database->quote($value);
                break;
            case FIELD::TYPE_DATE:
            case FIELD::TYPE_TIMESTAMP:
                if ($field->type == FIELD::TYPE_TIMESTAMP) {
                    $value = (is_numeric($value)) ? date("Y-m-d H:i:s T", $value) : $value;
                } elseif ($field->type == FIELD::TYPE_DATE) {
                    $value = (is_numeric($value)) ? date("Y-m-d", $value) : $value;
                }
                // Format the string then fall through to default
            default:
                if (!empty($value)) {
                    $conditionString = "($fieldName!=" .
                        $this->database->quote($value) .
                        " or $fieldName is null)";
                } else {
                    $conditionString = "$fieldName is not null";
                }
                break;
        }

        return $conditionString;
    }

    /**
     * Function that will be the query string for Grouping Query Conditions
     *
     * @param EntityDefinition $entityDefinition Definition for the entity being queried
     * @param Field $field The current field that we will handle to build the is_not_equal where condition
     * @param Array $condition The where condition that we are dealing with
     * @return string
     */
    private function buildGroupingQueryCondition(EntityDefinition $entityDefinition, $field, $condition)
    {
        $objectTable = $entityDefinition->getTable();
        $fieldName = $condition->fieldName;
        $value = $condition->value;
        $operator = $condition->operator;

        $conditionString = "";
        if (empty($value)) {
            if ($operator == Where::OPERATOR_EQUAL_TO) {
                $conditionString = "$fieldName is null";
            } else {
                $conditionString = "$fieldName is not null";
            }
        } else {
            $operatorSign = "";
            if ($operator == Where::OPERATOR_NOT_EQUAL_TO) {
                $operatorSign = "!";
            }

            $conditionString = "$fieldName{$operatorSign}=" . $this->database->quote($value);

            // If our operator is not equal to , then we need to add if fieldname is null with or operator
            if ($operator == Where::OPERATOR_NOT_EQUAL_TO) {
                $conditionString = "($conditionString  or $fieldName is null)";
            }
        }

        return $conditionString;
    }

    /**
     * Function that will be the query string for ObjectMulti Query Conditions
     *
     * @param EntityDefinition $entityDefinition Definition for the entity being queried
     * @param Field $field The current field that we will handle to build the is_not_equal where condition
     * @param Array $condition The where condition that we are dealing with
     * @return string
     */
    private function buildObjectMultiQueryCondition(EntityDefinition $entityDefinition, $field, $condition)
    {
        $objectTable = $entityDefinition->getTable();
        $value = $condition->value;
        $operator = $condition->operator;

        // This is a query string that is common for different condition operators
        $selectQueryString = "select object_id from object_associations
                                        where object_associations.object_id=$objectTable.id
                                        and type_id=" . $entityDefinition->getId() . "
                                        and field_id=" . $field->id;

        $conditionString = "";

        // If we are dealing with a condition with an empty value
        if (empty($value)) {
            if ($operator == Where::OPERATOR_EQUAL_TO) {
                $conditionString = "not EXISTS ($selectQueryString)";
            } else {
                $conditionString = "$objectTable.id in ($selectQueryString)";
            }
        } else {
            $objRef = Entity::decodeObjRef($value);
            $referenceObjType = null;
            $referenceId = null;

            /*
             * If we have successfully decoded the $value (e.g user:1:TestUser
             * Then we need to make sure we have refernce id and obj_type
             */
            if ($objRef && !empty($objRef['id']) && !empty($objRef['obj_type'])) {
                $referenceObjType = $objRef['obj_type'];
                $referenceId = $objRef['id'];
            } elseif ($field->subtype) {
                /*
                 * If the $value provided is the actual value of the where condition
                 * Then we will just use the field's subtype as our referenced objType
                 */
                $referenceObjType = $field->subtype;
                $referenceId = $value;
            }

            // If we have referencedObjType then we can now build the where condition
            if ($referenceObjType) {
                // Get the definition of the referenced objType
                $refDef = $this->getDefinition($referenceObjType);

                $prefixQueryString = "";
                if ($operator == Where::OPERATOR_EQUAL_TO) {
                    $prefixQueryString = "EXISTS";
                } else {
                    $prefixQueryString = "$objectTable.id not in";
                }

                if ($refDef && $refDef->getId() && $referenceId) {
                    $conditionString = "$prefixQueryString ($selectQueryString and assoc_type_id=" . $refDef->getId() . "
                                    and assoc_object_id=" . $this->database->quote($referenceId) . ")";
                } else {
                    // only query associated subtype if there is no referenced id provided
                    $conditionString = "$prefixQueryString ($selectQueryString and assoc_type_id=" . $refDef->getId() . ")";
                }
            }
        }

        return $conditionString;
    }

    /**
     * Get ids of all child entries in a parent-child relationship
     *
     * This function may be over-ridden in specific indexes for performance reasons
     *
     * @param string $table The table to query
     * @param string $parent_field The field containing the id of the parent entry
     * @param int $childId The id of the child element
     */
    public function getHeiarchyDownGrp(Field $field, $childId)
    {
        $ret = array();

        // If not heiarchy then just return this
        if (empty($field->fkeyTable["parent"])) {
            return array($childId);
        }

        $sql = "WITH RECURSIVE children AS
                (
                    -- non-recursive term
                    SELECT id FROM {$field->subtype} WHERE id=:heiarchy_id
                    UNION ALL
                    -- recursive term
                    SELECT {$field->subtype}.id
                    FROM {$field->subtype}
                    JOIN children AS chld
                        ON ({$field->subtype}.{$field->fkeyTable["parent"]} = chld.id)
                )
                SELECT id
                FROM children";

        $result = $this->database->query($sql, ['heiarchy_id' => $childId]);
        foreach ($result->fetchAll() as $row) {
            $ret[] = $row["id"];
        }

        return $ret;
    }

    /**
     * Set aggregation data
     *
     * @param EntityDefinition $entityDefinition Definition for the entity being queried
     * @param AggregationInterface $agg
     * @param Results $results Results that will be used where we will set the aggregate data
     * @param string $objectTable The actual table we are querying
     * @param string $conditionQuery The query condition that will be used for filtering
     */
    private function queryAggregation(EntityDefinition $entityDefinition, AggregationInterface $agg, Results $results, $conditionQuery)
    {
        $objectTable = $entityDefinition->getTable();
        $fieldName = $agg->getField();
        $aggTypeName = $agg->getTypeName();

        // Make sure that we have a valid field name
        if (!$fieldName) {
            return false;
        }

        $orderBy = "";
        $queryFields = "min($fieldName) as agg_min,
                        max($fieldName) as agg_max,
                        avg($fieldName) as agg_avg,
                        sum($fieldName) as agg_sum";

        // If we are dealing with aggregate terms, then we need to group the results by $fieldName
        if ($aggTypeName === "terms") {
            $queryFields = "distinct($fieldName) as agg_distinct, count($fieldName) as cnt";
            $orderBy = "GROUP BY $fieldName";
        }

        // Add "and" operator in the $conditionQuery if it is not empty
        if ($conditionQuery) {
            $conditionQuery = "and ($conditionQuery)";
        }

        $sql = "SELECT $queryFields FROM $objectTable WHERE id is not null $conditionQuery $orderBy";

        $result = $this->database->query($sql);

        // Make sure that we have results before we process the aggregates
        if ($result->rowCount()) {
            $data = null;

            // Determine which type of aggregate we will use to process the results
            switch ($aggTypeName) {
                case 'min':
                case 'sum':
                case 'avg':
                    $row = $result->fetch();
                    $data = $row["agg_$aggTypeName"];
                    break;
                case 'terms':
                    $data = [];
                    foreach ($result->fetchAll() as $row) {
                        $data[] = ["count" => $row["cnt"], "term" => $row[$fieldName]];
                    }
                    break;
                case 'stats':
                    $row = $result->fetch();
                    $data = [
                        "min" => $row["agg_min"],
                        "max" => $row["agg_max"],
                        "avg" => $row["agg_avg"],
                        "sum" => $row["agg_sum"],
                        "count" => $results->getTotalNum()
                    ];
                    break;
                case 'count':
                    $data = $results->getTotalNum();
                    break;
            }

            $results->setAggregation($agg->getName(), $data);
        }
    }

    /**
     * Handle converting boolean to strings
     *
     * @param Field $field
     * @param mixed $value
     */
    public function sanitizeWhereCondition(Field $field, $value)
    {
        $value = parent::sanitizeWhereCondition($field, $value);

        // Convert bool to string
        if ($field->type == Field::TYPE_BOOL) {
            return ($value === true) ? 'true' : 'false';
        }

        return $value;
    }
}
