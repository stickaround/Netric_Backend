<?php

/**
 * Relational Database implementation of indexer for querying objects
 */
namespace Netric\EntityQuery\Index;

use Netric\EntityDefinition\Field;
use Netric\EntityQuery;
use Netric\EntityQuery\Where;
use Netric\EntityQuery\Results;
use Netric\EntityQuery\Aggregation;
use Netric\EntityDefinition\EntityDefinition;
use Netric\Account\Account;
use Netric\Entity\Entity;
use Netric\EntityQuery\Aggregation\AggregationInterface;

use Netric\Db\Relational\RelationalDbFactory;
use Netric\Db\Relational\Exception\DatabaseQueryException;

class EntityQueryIndexRdb extends IndexAbstract implements IndexInterface
{
    /**
     * Handle to database
     *
     * @var RelationalDbInterface
     */
    private $database = null;

    /**
     * Contains the parameter values that will be used to build the where clause
     */
    private $conditionParams = [];

    /**
     * The entity definition that is used to build the query strings
     */
    private $entityDefintion = null;

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
        $tsVector = [];
        foreach ($fields as $field) {
            if ($field->type != FIELD::TYPE_GROUPING_MULTI && $field->type != FIELD::TYPE_OBJECT_MULTI) {
                $tsVector[] = strtolower($entity->getValue($field->name));
            }
        }

        $sql = "UPDATE $tableName SET tsv_fulltext=to_tsvector('english', '" . implode(" ", $tsVector) . "') ";
        $sql .= "WHERE id=:id";

        /*
         * We will be using rdb::query() here instead of rdb::update()
         * since we are using to_vector() pgsql function and not updating a field using a normal data
         */
        $this->database->query($sql, ["id" => $entity->getId()]);
        return true;
    }

    /**
     * Delete an object from the index
     *
     * @param string $objectId Unique id of object to delete
     * @return bool true on success, false on failure
     */
    public function delete($objectId)
    {
        // Nothing need be done because we are currently storing data in pgsql
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
        // Make sure that we have an entity definition before executing a query
        $this->entityDefintion = $this->getDefinition($query->getObjType());

        // Should never happen, but just in case if we do not have an entity definition throw an exception
        if (!$this->entityDefintion) {
            throw new \RuntimeException("No entity definition" . var_export($query->toArray(), true));
        }

        // Get table to query
        $objectTable = $this->entityDefintion->getTable();

        // Make sure that these values are clean
        $this->conditionParams = [];
        $conditions = [
            Where::COMBINED_BY_AND => [],
            Where::COMBINED_BY_OR => []
        ];

        // Flag that will determine if we have set a f_deleted field in the query conditions
        $fDeletedCondSet = false;

        // Start building the condition string
        $conditionString = "";
        $queryConditions = $query->getWheres();

        if (count($queryConditions)) {
            /*
             * This will contain conditions strings from buildAdvancedConditionString()
             * We will not empty this array if the next condition blogic is an operator "or"
             */
            $advConditions = [];

            // Set the default Blogic to and
            $conditionBlogic = Where::COMBINED_BY_AND;

            // Loop thru the query conditions and check for special fields
            foreach ($queryConditions as $idx => $condition) {
                // If we have a full text condition, then we need to set it up properly
                if ($condition->fieldName === "*") {
                    $conditions[Where::COMBINED_BY_AND][] = "(tsv_fulltext @@ plainto_tsquery(:full_text))";
                    $this->conditionParams["full_text"] = $condition->value;
                    continue;
                }

                if ($condition->fieldName === "f_deleted") {
                    $fDeletedCondSet = true;
                }

                $advConditionString = $this->buildAdvancedConditionString($condition);

                // Make sure that we have built an advanced condition string
                if (!empty($advConditionString)) {
                    $advConditions[] = $advConditionString;

                    // We will always use the bLogic of the first condition
                    if (count($advConditions) === 1) {
                        $conditionBlogic = $condition->bLogic;
                    }

                    $nextCondition = $queryConditions[$idx+1];
                    if (!empty($nextCondition) && $nextCondition->bLogic == Where::COMBINED_BY_OR) {
                        /*
                         * If the $nextCondition bLogic is an operator "or" then we will set it as a group
                         * So we will continue with the next condition
                         */
                        continue;
                    } else {
                        $conditions[$conditionBlogic][] = "(" . implode(" or ", $advConditions) .")";
                    }

                    // Clear the advanced conditions array
                    $advConditions = [];
                }
            }

            // After populating the $conditions then we need to create the conditionString
            if (!empty($conditions[Where::COMBINED_BY_AND])) {
                $conditionString = implode(" and ", $conditions[Where::COMBINED_BY_AND]);
            }

            if (!empty($conditions[Where::COMBINED_BY_OR])) {
                if ($conditionString) {
                    $conditionString .= " or ";
                }

                $conditionString .= implode(" or ", $conditions[Where::COMBINED_BY_OR]);
            }
        }

        /*
         * If there is no f_deleted field condition set and entityDefinition has f_deleted field
         * We will make sure that we will get the non-deleted records
         */
        if (!$fDeletedCondSet && $this->entityDefintion->getField("f_deleted")) {
            // If $conditionString is not empty, then we will just append the "and" blogic
            if (!empty($conditionString))
                $conditionString .= " and ";

            $conditionString .= "(f_deleted=:f_deleted)";
            $this->conditionParams["f_deleted"] = false;
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

        if (!empty($query->getOffset())) {
            $sql .= " OFFSET {$query->getOffset()}";
        }

        // Get fields for this object type (used in decoding multi-valued fields)
        $ofields = $this->entityDefintion->getFields();

        // Create results object
        if ($results === null) {
            $results = new Results($query, $this);
        } else {
            $results->clearEntities();
        }

        $result = $this->database->query($sql, $this->conditionParams);

        foreach ($result->fetchAll() as $row) {

            // Decode multival fields into arrays of values
            foreach ($ofields as $fname => $fdef) {
                if ($fdef->type === "fkey_multi" || $fdef->type === "object_multi") {
                    if (isset($row[$fname])) {
                        $dec = json_decode($row[$fname], true);
                        if ($dec !== false) {
                            $row[$fname] = $dec;
                        }
                    }
                }

                if ($fdef->type === "fkey" || $fdef->type === "object"
                    || $fdef->type === "fkey_multi" || $fdef->type === "object_multi"
                ) {
                    if (isset($row[$fname . "_fval"])) {
                        $dec = json_decode($row[$fname . "_fval"], true);
                        if ($dec !== false) {
                            $row[$fname . "_fval"] = $dec;
                        }
                    }
                }
            }

            // Set and add entity
            $ent = $this->entityFactory->create($this->entityDefintion->getObjType());
            $ent->fromArray($row);
            $ent->resetIsDirty();
            $results->addEntity($ent);
        }

        // Get total num
        // ----------------------------------------
        $sql = "SELECT count(*) as cnt FROM $objectTable";

        if (!empty($conditionString)) {
            $sql .= " WHERE $conditionString";
        }

        $result = $this->database->query($sql, $this->conditionParams);
        if ($result->rowCount()) {
            $row = $result->fetch();
            $results->setTotalNum($row["cnt"]);
        }

        // Get aggregations
        // ----------------------------------------
        if ($query->hasAggregations()) {
            $aggregations = $query->getAggregations();
            foreach ($aggregations as $agg) {
                $this->queryAggregation($agg, $results, $objectTable, $conditionString);
            }
        }

        return $results;
    }

    /**
     * Process filter conditions
     *
     * @param Array $condition The where condition that we are dealing with
     * @throws \RuntimeException If a problem is encountered with the query
     */
    private function buildAdvancedConditionString($condition)
    {
        $fieldName = $condition->fieldName;
        $operator = $condition->operator;
        $value = $condition->value;

        // Should never happen, but just in case if operator is missing throw an exception
        if (!$operator) {
            throw new \RuntimeException("No operator provided for " . var_export($condition, true));
        }

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

        // Get field
        $origField = $this->entityDefintion->getField($parts[0]);

        // If we do not have a field then throw an exception
        if (!$origField) {
            throw new \RuntimeException("Could not get field {$parts[0]}");
        }

        // Make a copy in case we need change the type to object_reference
        $field = clone $origField;

        // Sanitize and replace environment variables like 'current_user' to concrete vals
        $value = $this->sanitizeWhereCondition($field, $value);

        // Generate the $paramName for this condition and make sure it is unique
        $paramName = $this->generateParamName($fieldName);

        $conditionString = "";
        switch ($operator) {
            case 'is_equal':
                $conditionString = $this->buildIsEqual($field, $condition);
                break;
            case 'is_not_equal':
                $conditionString = $this->buildIsNotEqual($field, $condition);
                break;
            case 'is_greater':
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

                        $conditionString = "$fieldName>:$paramName";
                        $this->conditionParams[$paramName] = $value;
                        break;
                }
                break;
            case 'is_less':
                switch ($field->type) {
                    case FIELD::TYPE_OBJECT_MULTI:
                    case FIELD::TYPE_OBJECT:
                    case FIELD::TYPE_GROUPING_MULTI:
                        break;
                    case FIELD::TYPE_TEXT:
                        break;
                    default:
                        if ($field->type == FIELD::TYPE_TIMESTAMP) {
                            $value = (is_numeric($value)) ? date("Y-m-d H:i:s T", $value) : $value;
                        } elseif ($field->type == FIELD::TYPE_DATE) {
                            $value = (is_numeric($value)) ? date("Y-m-d", $value) : $value;
                        }

                        $conditionString = "$fieldName<:$paramName";
                        $this->conditionParams[$paramName] = $value;
                        break;
                }
                break;
            case 'is_greater_or_equal':
                switch ($field->type) {
                    case FIELD::TYPE_OBJECT:
                        if ($field->subtype) {
                            $children = $this->getHeiarchyDownObj($field->subtype, $value);

                            foreach ($children as $child) {
                                $childParam = $this->generateParamName($fieldName);
                                $multiCond[] = "$fieldName=:$childParam";
                                $this->conditionParams[$childParam] = $child;
                            }

                            $conditionString = "(" . implode(" or ", $multiCond) . ")";
                            break;
                        }
                        break;
                    case FIELD::TYPE_OBJECT_MULTI:
                    case FIELD::TYPE_GROUPING_MULTI:
                        break;
                    case FIELD::TYPE_TEXT:
                        break;
                    default:
                        if ($field->type == FIELD::TYPE_TIMESTAMP) {
                            $value = (is_numeric($value)) ? date("Y-m-d H:i:s T", $value) : $value;
                        } elseif ($field->type == FIELD::TYPE_DATE) {
                            $value = (is_numeric($value)) ? date("Y-m-d", $value) : $value;
                        }

                        $conditionString = "$fieldName>=:$paramName";
                        $this->conditionParams[$paramName] = $value;
                        break;
                }
                break;
            case 'is_less_or_equal':
                switch ($field->type) {
                    case FIELD::TYPE_OBJECT:
                        if (!empty($field->subtype)
                            && $this->entityDefintion->parentField == $fieldName
                            && is_numeric($value)) {
                            $refDef = $this->getDefinition($field->subtype);
                            $refDefTable = $refDef->getTable(true);

                            if ($refDef->parentField) {
                                $conditionString = "$fieldName in (WITH RECURSIVE children AS
												(
													-- non-recursive term
													SELECT id FROM $refDefTable WHERE id=:$paramName
													UNION ALL
													-- recursive term
													SELECT $refDefTable.id
													FROM $refDefTable
													JOIN children AS chld
														ON ($refDefTable.{$refDef->parentField}=chld.id)
												)
												SELECT id
												FROM children)";

                                $this->conditionParams[$paramName] = $value;
                            }
                        }
                        break;
                    case FIELD::TYPE_OBJECT_MULTI:
                    case FIELD::TYPE_GROUPING_MULTI:
                        break;
                    case FIELD::TYPE_TEXT:
                        break;
                    default:
                        if ($field->type == FIELD::TYPE_TIMESTAMP) {
                            $value = (is_numeric($value)) ? date("Y-m-d H:i:s T", $value) : $value;
                        } elseif ($field->type == FIELD::TYPE_DATE) {
                            $value = (is_numeric($value)) ? date("Y-m-d", $value) : $value;
                        }

                        $conditionString = "$fieldName<=:$paramName";
                        $this->conditionParams[$paramName] = $value;
                        break;
                }
                break;
            case 'begins':
            case 'begins_with':
                switch ($field->type) {
                    case FIELD::TYPE_TEXT:
                        if ($field->subtype) {
                            $conditionString = "lower($fieldName) like :$paramName";
                            $this->conditionParams[$paramName] = strtolower("$value%");
                        } else {
                            $conditionString = "to_tsvector($fieldName) @@ plainto_tsquery(:$paramName)";
                            $this->conditionParams[$paramName] = "$value*";
                        }
                        break;
                    default:
                        break;
                }
                break;
            case 'contains':
                switch ($field->type) {
                    case FIELD::TYPE_TEXT:
                        if ($field->subtype) {
                            $conditionString = "lower($fieldName) like :$paramName";
                            $this->conditionParams[$paramName] = strtolower("%$value%");
                        } else {
                            $conditionString = "to_tsvector($fieldName) @@ plainto_tsquery(:$paramName)";
                            $this->conditionParams[$paramName] = $value;
                        }

                        break;
                    default:
                        break;
                }
                break;
            case 'day_is_equal':
                if ($field->type == FIELD::TYPE_DATE || $field->type == FIELD::TYPE_TIMESTAMP) {
                    switch ($value) {
                        case '<%current_day%>':
                            $conditionString = "extract(day from $fieldName)=extract('day' from now())";
                            break;
                        default:
                            $conditionString = "extract(day from $fieldName)=:$paramName";
                            $this->conditionParams[$paramName] = $value;
                            break;
                    }
                }
                break;
            case 'month_is_equal':
                if ($field->type == FIELD::TYPE_DATE || $field->type == FIELD::TYPE_TIMESTAMP) {
                    switch ($value) {
                        case '<%current_month%>':
                            $conditionString = "extract(month from $fieldName)=extract('month' from now())";
                            break;
                        default:
                            $conditionString = "extract(month from $fieldName)=:$paramName";
                            $this->conditionParams[$paramName] = $value;
                            break;
                    }
                }
                break;
            case 'year_is_equal':
                if ($field->type == FIELD::TYPE_DATE || $field->type == FIELD::TYPE_TIMESTAMP) {
                    switch ($value) {
                        case '<%current_year%>':
                            $conditionString = "extract(year from $fieldName)=extract('year' from now())";
                            break;
                        default:
                            $conditionString = "extract(year from $fieldName)=:$paramName";
                            $this->conditionParams[$paramName] = $value;
                            break;
                    }
                }
                break;
            case 'last_x_days':
                if ($field->type == FIELD::TYPE_DATE || $field->type == FIELD::TYPE_TIMESTAMP && is_numeric($value)) {
                    $conditionString = "$fieldName>=(now()-INTERVAL '$value days')";
                }
                break;
            case 'last_x_weeks':
                if ($field->type == FIELD::TYPE_DATE || $field->type == FIELD::TYPE_TIMESTAMP && is_numeric($value)) {
                    $conditionString = "$fieldName>=(now()-INTERVAL '$value weeks')";
                }
                break;
            case 'last_x_months':
                if ($field->type == FIELD::TYPE_DATE || $field->type == FIELD::TYPE_TIMESTAMP && is_numeric($value)) {
                    $conditionString = "$fieldName>=(now()-INTERVAL '$value months')";
                }
                break;
            case 'last_x_years':
                if ($field->type == FIELD::TYPE_DATE || $field->type == FIELD::TYPE_TIMESTAMP && is_numeric($value)) {
                    $conditionString = "$fieldName>=(now()-INTERVAL '$value years')";
                }
                break;
            case 'next_x_days':
                if ($field->type == FIELD::TYPE_DATE || $field->type == FIELD::TYPE_TIMESTAMP && is_numeric($value)) {
                    $conditionString = "$fieldName>=now() and $fieldName<=(now()+INTERVAL '$value days')";
                }
                break;
            case 'next_x_weeks':
                if ($field->type == FIELD::TYPE_DATE || $field->type == FIELD::TYPE_TIMESTAMP && is_numeric($value)) {
                    $conditionString = "$fieldName>=now() and $fieldName<=(now()+INTERVAL '$value weeks')";
                }
                break;
            case 'next_x_months':
                if ($field->type == FIELD::TYPE_DATE || $field->type == FIELD::TYPE_TIMESTAMP && is_numeric($value)) {
                    $conditionString = "$fieldName>=now() and $fieldName<=(now()+INTERVAL '$value months')";
                }
                break;
            case 'next_x_years':
                if ($field->type == FIELD::TYPE_DATE || $field->type == FIELD::TYPE_TIMESTAMP && is_numeric($value)) {
                    $conditionString = "$fieldName>=now() and $fieldName<=(now()+INTERVAL '$value years')";
                }
                break;
        }

        return $conditionString;
    }

    /**
     * Add conditions for "is_eqaul" operator
     *
     * @param type $field The current field that we will handle to build the is_equal where condition
     * @param Array $condition The where condition that we are dealing with
     */
    private function buildIsEqual($field, $condition)
    {
        $objectTable = $this->entityDefintion->getTable();
        $fieldName = $condition->fieldName;
        $value = $condition->value;

        // Generate the $paramName for this condition and make sure it is unique
        $paramName = $this->generateParamName($fieldName);

        $conditionString = "";
        switch ($field->type) {
            case FIELD::TYPE_OBJECT:
                if ($field->subtype) {
                    if ($value) {
                        $conditionString = "$fieldName=:$paramName";
                        $this->conditionParams[$paramName] = $value;
                    } else {
                        $conditionString = "$fieldName is null";
                    }
                }
                break;
            case FIELD::TYPE_OBJECT_MULTI:
                // We need a unique param for type_id since it is possible to query 2 or more object multi fields
                $fieldIdParam = $this->generateParamName("field_id");

                if (empty($value)) {
                    $conditionString = "not EXISTS (select 1 from object_associations
                                        where object_associations.object_id=$objectTable.id
                                        and type_id=:type_id
                                        and field_id=:$fieldIdParam)";
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

                        $assocTypeParam = $this->generateParamName("assoc_type_id");
                        $this->conditionParams[$assocTypeParam] = $refDef->getId();

                        if ($refDef && $refDef->getId() && $referenceId) {
                            $assocObjParam = $this->generateParamName("assoc_object_id");
                            $this->conditionParams[$assocObjParam] = $referenceId;

                            $conditionString = "EXISTS (select 1 from object_associations
                                    where object_associations.object_id=$objectTable.id
                                    and type_id=:type_id and field_id=:$fieldIdParam
                                    and assoc_type_id=:$assocTypeParam
                                    and assoc_object_id=:$assocObjParam)";
                        } else {
                            // only query associated subtype if there is no referenced id provided
                            $conditionString = "EXISTS (select 1 from object_associations
                                    where object_associations.object_id=$objectTable.id and
                                    type_id=:type_id and field_id=:$fieldIdParam
                                    and assoc_type_id=:$assocTypeParam)";
                        }
                    }
                }
                
                if (!empty($conditionString)) {
                    $this->conditionParams[$fieldIdParam] = $field->id;
                    $this->conditionParams["type_id"] = $this->entityDefintion->getId();
                }
                break;
            case 'object_dereference':
                // TODO: Ask sky about what is object_dereference
                if ($field->subtype && isset($refField)) {
                    // Create subquery
                    /*$subQuery = new EntityQuery($field->subtype);
                    $subQuery->where($refField)->equals($value);
                    $subIndex = new EntityQueryIndexRdb($this->account);
                    $tmp_obj_cnd_str = $subIndex->buildAdvancedConditionString($subQuery);
                    $refDef = $this->getDefinition($field->subtype);

                    if ($value == "" || $value == "NULL") {
                        $buf .= " " . $objectTable . ".$fieldName not in (select id from " . $refDef->getTable() . "
                                                                                where $tmp_obj_cnd_str) ";
                    } else {
                        $buf .= " " . $objectTable . ".$fieldName in (select id from " . $refDef->getTable() . "
                                                                                where $tmp_obj_cnd_str) ";
                    }*/
                }
                break;
            case FIELD::TYPE_GROUPING_MULTI:
                $multiCond = [];
                $fkeyTableRef = $field->fkeyTable['ref_table']['ref'];
                $fkeyRefParam = $this->generateParamName($fkeyTableRef);

                // Check if the fkey table has a parent
                if (isset($field->fkeyTable["parent"]) && is_numeric($value)) {
                    $children = $this->getHeiarchyDownGrp($field, $value);

                    // Make sure that we have a children
                    if (!empty($children)) {
                        foreach ($children as $child) {
                            $childParam = $this->generateParamName($fkeyTableRef);
                            $multiCond[] = "$fkeyTableRef=:$childParam";
                            $this->conditionParams[$childParam] = $child;
                        }
                    } else {
                        $multiCond[] = "$fkeyTableRef=:$fkeyRefParam";
                        $this->conditionParams[$fkeyRefParam] = $value;
                    }
                } elseif (!empty($value)) {
                    $multiCond[] = "$fkeyTableRef=:$fkeyRefParam";
                    $this->conditionParams[$fkeyRefParam] = $value;
                }

                $thisfld = $field->fkeyTable['ref_table']["this"];
                $reftbl = $field->fkeyTable['ref_table']['table'];

                if (empty($value)) {
                    $conditionString = " NOT EXISTS (select 1 from  $reftbl where $reftbl.$thisfld=$objectTable.id) ";
                } else {
                    $conditionString = " EXISTS (select 1 from  $reftbl where $reftbl.$thisfld=$objectTable.id
                            and (" . implode(" or ", $multiCond) . ")) ";
                }
                break;
            case FIELD::TYPE_GROUPING:
                if (empty($value)) {
                    $conditionString = "$fieldName is null";
                } else {
                    $multiCond = [];

                    if (!empty($field->fkeyTable["parent"]) && is_numeric($value)) {
                        $children = $this->getHeiarchyDownGrp($field, $value);

                        foreach ($children as $child) {
                            $childParam = $this->generateParamName($fieldName);
                            $multiCond[] = "$fieldName=:$childParam";
                            $this->conditionParams[$childParam] = $child;
                        }
                    } else {
                        $multiCond[] = "$fieldName=$paramName";
                        $this->conditionParams[$paramName] = $value;
                    }

                    $conditionString = "(" . implode(" or ", $multiCond) . ")";
                }
                break;
            case FIELD::TYPE_TEXT:
                if (empty($value)) {
                    $conditionString = "($fieldName is null OR $fieldName='')";
                } elseif ($field->subtype) {
                    $conditionString = "lower($fieldName)=:$paramName";
                    $this->conditionParams[$paramName] = strtolower($value);
                } else {
                    $conditionString = "to_tsvector($fieldName) @@ plainto_tsquery(:$paramName)";
                    $this->conditionParams[$paramName] = $value;
                }
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
                    $conditionString = "$fieldName=:$paramName";
                    $this->conditionParams[$paramName] = $value;
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
     * @param type $field The current field that we will handle to build the is_not_equal where condition
     * @param Array $condition The where condition that we are dealing with
     */
    private function buildIsNotEqual($field, $condition)
    {
        $objectTable = $this->entityDefintion->getTable();
        $fieldName = $condition->fieldName;
        $value = $condition->value;

        // Generate the $paramName for this condition and make sure it is unique
        $paramName = $this->generateParamName($fieldName);

        $conditionString = "";
        switch ($field->type) {
            case FIELD::TYPE_OBJECT:
                // Check if we are querying table directly, otherwise fall through to object_multi code
                if ($field->subtype) {
                    if (empty($value)) {
                        $conditionString = "$fieldName is not null";
                    } elseif (isset($field->subtype) && $this->entityDefintion->parentField == $fieldName && $value) {
                        $refDef = $this->getDefinition($field->subtype);
                        $refDefTable = $refDef->getTable(true);
                        $parentField = $refDef->parentField;

                        if ($refDef->parentField) {
                            $conditionString = "$fieldName not in (WITH RECURSIVE children AS
                                    (
                                        -- non-recursive term
                                        SELECT id FROM $refDefTable WHERE id=:$paramName
                                        UNION ALL
                                        -- recursive term
                                        SELECT $refDefTable.id
                                        FROM $refDefTable
                                        JOIN children AS chld
                                            ON ($refDefTable.$parentField = chld.id)
                                    )
                                    SELECT id
                                    FROM children)";
                            $this->conditionParams[$paramName] = $value;
                        }
                    } else {
                        $conditionString = "$fieldName!=:$paramName";
                        $this->conditionParams[$paramName] = $value;
                    }
                }
                break;

            case FIELD::TYPE_OBJECT_MULTI:
                // We need a unique param for type_id since it is possible to query 2 or more object multi fields
                $fieldIdParam = $this->generateParamName("field_id");

                if (empty($value)) {
                    $conditionString = "$objectTable.id in  (select 1 from object_associations
                                        where object_associations.object_id=$objectTable.id
                                        and type_id=:type_id
                                        and field_id=:$fieldIdParam)";
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

                        $assocTypeParam = $this->generateParamName("assoc_type_id");
                        $this->conditionParams[$assocTypeParam] = $refDef->getId();

                        if ($refDef && $refDef->getId() && $referenceId) {
                            $assocObjParam = $this->generateParamName("assoc_object_id");
                            $this->conditionParams[$assocObjParam] = $referenceId;

                            $conditionString = "$objectTable.id not in  (select object_id from object_associations
                                    where object_associations.object_id=$objectTable.id
                                    and type_id=:type_id and field_id=:$fieldIdParam
                                    and assoc_type_id=:$assocTypeParam
                                    and assoc_object_id=:$assocObjParam)";
                        } else {
                            // only query associated subtype if there is no referenced id provided
                            $conditionString = "$objectTable.id not in  (select object_id from object_associations
                                    where object_associations.object_id=$objectTable.id and
                                    type_id=:type_id and field_id=:$fieldIdParam
                                    and assoc_type_id=:$assocTypeParam)";
                        }
                    }
                }

                if (!empty($conditionString)) {
                    $this->conditionParams[$fieldIdParam] = $field->id;
                    $this->conditionParams["type_id"] = $this->entityDefintion->getId();
                }
                break;
            case 'object_dereference':
                /*$tmp_cond_str = "";
                if ($field->subtype && $refField) {
                    // Create subquery
                    $subQuery = new \Netric\EntityQuery($field->subtype);
                    $subQuery->where($refField, $operator, $value);
                    $subIndex = new \Netric\EntityQuery\Index\Pgsql($this->account);
                    $tmp_obj_cnd_str = $subIndex->buildAdvancedConditionString($subQuery);
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
                $fkeyRefParam = $this->generateParamName($fkeyTableRef);

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
                                $childParam = $this->generateParamName($fkeyTableRef);
                                $multiCond[] = "$fkeyTableRef=:$childParam";
                                $this->conditionParams[$childParam] = $child;
                            }
                        } else {
                            $multiCond[] = "$fkeyTableRef=:$fkeyRefParam";
                            $this->conditionParams[$fkeyRefParam] = $value;
                        }
                    } else {
                        $multiCond[] = "$fkeyTableRef=:$fkeyRefParam";
                        $this->conditionParams[$fkeyRefParam] = $value;
                    }

                    $conditionString = "$objectTable.id not in (select $fkeyRefField
                                                                  from $fkeyRefTable
                                                                  where " . implode(" or ", $multiCond) . ") ";
                }

                break;
            case FIELD::TYPE_GROUPING:
                if (empty($value)) {
                    $conditionString = "$fieldName is not null";
                } else {
                    $multiCond = [];

                    if (!empty($field->fkeyTable["parent"]) && is_numeric($value)) {
                        $children = $this->getHeiarchyDownGrp($field, $value);
                        foreach ($children as $child) {
                            $childParam = $this->generateParamName($fieldName);
                            $multiCond[] = "$fieldName!=:$childParam";
                            $this->conditionParams[$childParam] = $child;
                        }
                    } else {
                        $multiCond[] = "$fieldName!=$paramName";
                        $this->conditionParams[$paramName] = $value;
                    }

                    $conditionString = "((" . implode(" and ", $multiCond) . ")  or $fieldName is null)";
                }

                break;
            case FIELD::TYPE_TEXT:
                if (empty($value)) {
                    $conditionString = "($fieldName!='' AND $fieldName is not NULL)";
                } elseif ($field->subtype) {
                    $conditionString = "lower($fieldName)!=:$paramName";
                    $this->conditionParams[$paramName] = strtolower($value);
                } else {
                    $conditionString = " (to_tsvector($fieldName) @@ plainto_tsquery(:$paramName))='f'";
                    $this->conditionParams[$paramName] = $value;
                }
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
                    $conditionString = "($fieldName!=:$paramName or $fieldName is null)";
                    $this->conditionParams[$paramName] = $value;
                } else {
                    $conditionString = "$fieldName is not null";
                }
                break;
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

        $heiarchyIdParam = $this->generateParamName("heiarchy_id");
        $sql = "WITH RECURSIVE children AS
                (
                    -- non-recursive term
                    SELECT id FROM {$field->subtype} WHERE id=:$heiarchyIdParam
                    UNION ALL
                    -- recursive term
                    SELECT {$field->subtype}.id
                    FROM {$field->subtype}
                    JOIN children AS chld
                        ON ({$field->subtype}.{$field->fkeyTable["parent"]} = chld.id)
                )
                SELECT id
                FROM children";

        $result = $this->database->query($sql, [$heiarchyIdParam => $childId]);
        foreach ($result->fetchAll() as $row) {
            $ret[] = $row["id"];
        }

        return $ret;
    }

    /**
     * Set aggregation data
     *
     * @param AggregationInterface $agg
     * @param Results $res
     * @param string $objectTable The actual table we are querying
     * @param string $conditionQuery The query condition for filtering
     */
    private function queryAggregation(AggregationInterface $agg, Results &$res, $objectTable, $conditionQuery)
    {
        $data = null;

        switch ($agg->getTypeName()) {
            case 'terms':
                $data = $this->queryAggTerms($agg, $objectTable, $conditionQuery);
                break;
            case 'sum':
                $data = $this->queryAggSum($agg, $objectTable, $conditionQuery);
                break;
            case 'avg':
                $data = $this->queryAggAvg($agg, $objectTable, $conditionQuery);
                break;
            case 'min':
                $data = $this->queryAggMin($agg, $objectTable, $conditionQuery);
                break;
            case 'stats':
                $data = $this->queryAggStats($agg, $objectTable, $conditionQuery);
                if ($data) {
                    $data['count'] = $res->getTotalNum();
                }
                break;
            case 'count':
                $data = $res->getTotalNum();
                break;
        }

        if ($data) {
            $res->setAggregation($agg->getName(), $data);
        }
    }

    /**
     * Set terms aggregation - basically a select distinct
     *
     * @param AggregationInterface $agg
     * @param string $objectTable The actual table we are querying
     * @param string $conditionQuery The query condition for filtering
     */
    private function queryAggTerms(AggregationInterface $agg, $objectTable, $conditionQuery)
    {
        $fieldName = $agg->getField();

        if (!$fieldName) {
            return false;
        }

        $retData = array();
        $query = "select distinct($fieldName), count($fieldName) as cnt from $objectTable where id is not null";

        if ($conditionQuery) {
            $query .= " and $conditionQuery";
        }

        $query .= " GROUP BY $fieldName";
        $result = $this->database->query($query, $this->conditionParams);

        foreach ($result->fetchAll() as $row) {
            $retData[] = array(
                "count" => $row["cnt"],
                "term" => $row[$fieldName],
            );
        }

        return $retData;
    }

    /**
     * Set sum aggregation
     *
     * @param AggregationInterface $agg
     * @param string $objectTable The actual table we are querying
     * @param string $conditionQuery The query condition for filtering
     */
    private function queryAggSum(AggregationInterface $agg, $objectTable, $conditionQuery)
    {
        $fieldName = $agg->getField();

        if (!$fieldName) {
            return false;
        }

        $query = "select sum($fieldName) as amount from $objectTable where id is not null";
        if ($conditionQuery) {
            $query .= " and $conditionQuery";
        }

        $result = $this->database->query($query, $this->conditionParams);
        if ($result->rowCount()) {
            $row = $result->fetch();
            return $row["amount"];
        }

        return false;
    }

    /**
     * Set sum aggregation
     *
     * @param AggregationInterface $agg
     * @param string $objectTable The actual table we are querying
     * @param string $conditionQuery The query condition for filtering
     */
    private function queryAggAvg(AggregationInterface $agg, $objectTable, $conditionQuery)
    {
        $fieldName = $agg->getField();

        if (!$fieldName) {
            return false;
        }

        $query = "select avg($fieldName) as amount from $objectTable where id is not null";
        if ($conditionQuery) {
            $query .= " and $conditionQuery";
        }

        $result = $this->database->query($query, $this->conditionParams);
        if ($result->rowCount()) {
            $row = $result->fetch();
            return $row["amount"];
        }

        return false;
    }

    /**
     * Set sum aggregation
     *
     * @param AggregationInterface $agg
     * @param string $objectTable The actual table we are querying
     * @param string $conditionQuery The query condition for filtering
     */
    private function queryAggMin(AggregationInterface $agg, $objectTable, $conditionQuery)
    {
        $fieldName = $agg->getField();

        if (!$fieldName) {
            return false;
        }

        $query = "select min($fieldName) as amount from $objectTable where id is not null";
        if ($conditionQuery) {
            $query .= " and $conditionQuery";
        }

        $result = $this->database->query($query, $this->conditionParams);

        if ($result->rowCount()) {
            $row = $result->fetch();
            return $row["amount"];
        }

        return false;
    }

    /**
     * Set sum aggregation
     *
     * @param AggregationInterface $agg
     * @param string $objectTable The actual table we are querying
     * @param string $conditionQuery The query condition for filtering
     */
    private function queryAggStats(AggregationInterface $agg, $objectTable, $conditionQuery)
    {
        $fieldName = $agg->getField();

        if (!$fieldName) {
            return false;
        }

        $query = "select "
            . "min($fieldName) as mi, "
            . "max($fieldName) as ma, "
            . "avg($fieldName) as av, "
            . "sum($fieldName) as su "
            . "FROM $objectTable where id is not null ";

        if ($conditionQuery) {
            $query .= " and ($conditionQuery) ";
        }

        $result = $this->database->query($query, $conditionQuery);
        if ($result->rowCount()) {
            $row = $result->fetch();
            return array(
                "min" => $row["mi"],
                "max" => $row["ma"],
                "avg" => $row["av"],
                "sum" => $row["su"],
                "count" => "" // set in calling class
            );
        }

        return false;
    }

    /**
     * Function that will generate a unique parameter name that will be used in where conditions
     *
     * @param $paramName The parameter name that will be used
     * @return mixed
     */
    private function generateParamName($paramName)
    {
        // If param is already existing in condition params, then we need to generate a new param
        if (!empty($this->conditionParams[$paramName])) {
            // This will make sure that there will be no duplicate paramName
            return $this->generateParamName($paramName . rand());
        }

        return $paramName;
    }
}