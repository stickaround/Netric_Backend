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
     * Contains the where conditions that will be used build the where clause
     */
    private $conditions = [
        Where::COMBINED_BY_AND => [],
        Where::COMBINED_BY_OR => []
    ];
    
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
            if ($field->type != FIELD::TYPE_GROUPING_MULTI && $field->type != FIELD::TYPE_OBJECT_MULTI)
                $tsVector[] = strtolower($entity->getValue($field->name));
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
     * @param string $id Unique id of object to delete
     * @return bool true on success, false on failure
     */
    public function delete($id)
    {
        // Nothing need be done because we are currently storing data in pgsql
        return true;
    }

    /**
     * Execute a query and return the results
     *
     * @param EntityQuery &$query The query to execute
     * @param Results $results Optional results set to use. Otherwise create new.
     * @return Results
     */
    protected function queryIndex(EntityQuery $query, Results $results = null)
    {
        // Make sure we will clear the values
        $this->conditionParams = [];
        $this->conditions = [
            Where::COMBINED_BY_AND => [],
            Where::COMBINED_BY_OR => []
        ];

        $def = $this->getDefinition($query->getObjType());

        // Set default f_deleted condition
        if ($def->getField("f_deleted")) {
            $conditions = $query->getWheres();

            // Check if f_deleted field is set in the where conditions
            $fDeletedCondSet = false;
            if (count($conditions)) {
                foreach ($conditions as $condition) {
                    if ($condition->fieldName === "f_deleted") {
                        $fDeletedCondSet = true;
                        break;
                    }
                }
            }

            // If there is no f_deleted field condition set, then we need to set it as false as default
            if (!$fDeletedCondSet)
                $this->conditions[Where::COMBINED_BY_AND][] = "f_deleted=false";
        }

        // Get table to query
        $objectTable = $def->getTable();

        // Start building the condition string
        $conditionString = "";
        if (count($query->getWheres()))
            $conditionString = $this->buildConditionString($query, $def);

        // Add order by
        $queryOrderByString = "";
        if (count($query->getOrderBy())) {
            $orderBy = $query->getOrderBy();
            foreach ($orderBy as $sort) {
                if ($queryOrderByString) {
                    $queryOrderByString .= ", ";
                }

                // TODO: check this
                // Replace name field to order by full name with path
                //if ($def->parentField && $def->getField("path"))
                //$order_fld = str_replace($this->obj->fields->listTitle, $this->obj->fields->listTitle."_full", $sortObj->fieldName);

                $queryOrderByString .= $sort->fieldName;
                $queryOrderByString .= " " . $sort->direction;
            }
        }

        // Start constructing query
        $sql = "SELECT * FROM $objectTable";

        if (!empty($conditionString))
            $sql .= " WHERE $conditionString";

        // Check if we have order by string
        if (!empty($queryOrderByString)) {
            $sql .= " ORDER BY $queryOrderByString";
        }

        // Check if we need to add limit
        if ($query->getLimit()) {
            $sql .= " LIMIT {$query->getLimit()}";
        }

        $sql .= " OFFSET {$query->getOffset()}";

        // Get fields for this object type (used in decoding multi-valued fields)
        $ofields = $def->getFields();

        // Create results object
        if ($results === null)
            $results = new Results($query, $this);
        else
            $results->clearEntities();

        $result = $this->database->query($sql, $this->conditionParams);
        foreach ($result->fetchAll() as $row) {
            $objecTable = $row['subtype'];
            $id = $row["id"];

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
                    || $fdef->type === "fkey_multi" || $fdef->type === "object_multi") {
                    if (isset($row[$fname . "_fval"])) {
                        $dec = json_decode($row[$fname . "_fval"], true);
                        if ($dec !== false) {
                            $row[$fname . "_fval"] = $dec;
                        }
                    }
                }
            }

            // Set and add entity
            $ent = $this->entityFactory->create($def->getObjType());
            $ent->fromArray($row);
            $ent->resetIsDirty();
            $results->addEntity($ent);
        }

        // Get total num
        // ----------------------------------------
        $sql = "SELECT count(*) as cnt FROM $objectTable";

        if (!empty($conditionString))
            $sql .= " WHERE $conditionString";

        $result = $this->database->query($sql, $this->conditionParams);
        if ($result->rowCount()) {
            $row = $result->fetch();
            $results->setTotalNum($row["cnt"]);
        }

        // Get aggregations
        // ----------------------------------------
        if ($query->hasAggregations()) {
            $aggregations = $query->getAggregations();
            foreach ($aggregations as $name => $agg) {
                $this->queryAggregation($agg, $results, $objectTable, $conditionString);
            }
        }

        return $results;
    }

    /**
     * Create a condition sql query string based on the query object
     *
     * @param EntityQuery $query
     * @param EntityDefinition $def
     * @return string
     */
    private function buildConditionString(EntityQuery $query, EntityDefinition $def)
    {
        // Check for full text
        $conditions = $query->getWheres();
        foreach ($conditions as $condition) {
            if ($condition->fieldName === "*") {

                $this->conditions[Where::COMBINED_BY_AND][] = "(tsv_fulltext @@ plainto_tsquery(:full_text))";
                $this->conditionParams["full_text"] = $condition->value;
                break;
            }
        }

        return $this->buildAdvancedConditionString($query, $def);
    }

    /**
     * Process filter conditions
     *
     * @param EntityQuery $query
     * @param EntityDefinition $def
     * @return string
     * @throws \RuntimeException If a problem is encountered with the query
     */
    public function buildAdvancedConditionString(EntityQuery $query, EntityDefinition $def = null)
    {
        $inOrGroup = false;
        $conditions = $query->getWheres();

        if ($def == null)
            $def = $this->getDefinition($query->getObjType());

        // Get table to query
        $objectTable = $def->getTable();

        if (count($conditions)) {
            foreach ($conditions as $condition) {
                $blogic = $condition->bLogic;
                $fieldName = $condition->fieldName;
                $operator = $condition->operator;
                $value = $condition->value;

                // Should never happen, but just in case if operator is missing throw an exception
                if (!$operator)
                    throw new \RuntimeException("No operator provided for " . var_export($condition, true));

                // Skip full text because it is already handled in buildConditionString()
                if ($fieldName === "*")
                    continue;

                // Look for associated object conditions
                $parts = array($fieldName);
                if (strpos($fieldName, '.'))
                    $parts = explode(".", $fieldName);

                if (count($parts) > 1) {
                    $fieldName = $parts[0];
                    $ref_field = $parts[1];
                    $field->type = "object_dereference";
                } else {
                    $ref_field = "";
                }

                // Get field
                $origField = $def->getField($parts[0]);

                // If we do not have a field then throw an exception
                if (!$origField)
                    throw new \RuntimeException("Could not get field " . $query->getObjType() . ":" . $parts[0]);

                // Make a copy in case we need change the type to object_reference
                $field = clone $origField;

                // Sanitize and replace environment variables like 'current_user' to concrete vals
                $value = $this->sanitizeWhereCondition($field, $value);

                // Generate the $paramName for this condition and make sure it is unique
                $paramName = $this->generateParamName($fieldName);

                if ($value !== "" && $value !== null) {
                    switch ($operator) {
                        case 'is_equal':
                            $this->buildIsEqual($field, $condition, $def);
                            break;
                        case 'is_not_equal':
                            $this->buildIsNotEqual($field, $condition, $def);
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

                                    $this->conditions[$blogic][] = "$fieldName>:$paramName";
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
                                    if ($field->type == FIELD::TYPE_TIMESTAMP)
                                        $value = (is_numeric($value)) ? date("Y-m-d H:i:s T", $value) : $value;
                                    elseif ($field->type == FIELD::TYPE_DATE)
                                        $value = (is_numeric($value)) ? date("Y-m-d", $value) : $value;

                                    $this->conditions[$blogic][] = "$fieldName<:$paramName";
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
                                            $multiCond[] =  "$fieldName=:$childParam";
                                            $this->conditionParams[$childParam] = $child;
                                        }

                                        $this->conditions[$blogic][] = "(" . implode(" or ", $multiCond) . ")";
                                        break;
                                    }
                                    break;
                                case FIELD::TYPE_OBJECT_MULTI:
                                case FIELD::TYPE_GROUPING_MULTI:
                                    break;
                                case FIELD::TYPE_TEXT:
                                    break;
                                default:
                                    if ($field->type == FIELD::TYPE_TIMESTAMP)
                                        $value = (is_numeric($value)) ? date("Y-m-d H:i:s T", $value) : $value;
                                    elseif ($field->type == FIELD::TYPE_DATE)
                                        $value = (is_numeric($value)) ? date("Y-m-d", $value) : $value;

                                    $this->conditions[$blogic][] = "$fieldName>=:$paramName";
                                    $this->conditionParams[$paramName] = $value;
                                    break;
                            }
                            break;
                        case 'is_less_or_equal':
                            switch ($field->type) {
                                case FIELD::TYPE_OBJECT:
                                    if (!empty($field->subtype) && $def->parentField == $fieldName && is_numeric($value)) {
                                        $refDef = $this->getDefinition($field->subtype);
                                        $refDefTable = $refDef->getTable(true);

                                        if ($refDef->parentField) {
                                            $this->conditions[$blogic][] = "$fieldName in (WITH RECURSIVE children AS
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
                                    if ($field->type == FIELD::TYPE_TIMESTAMP)
                                        $value = (is_numeric($value)) ? date("Y-m-d H:i:s T", $value) : $value;
                                    elseif ($field->type == FIELD::TYPE_DATE)
                                        $value = (is_numeric($value)) ? date("Y-m-d", $value) : $value;

                                    $this->conditions[$blogic][] = "$fieldName<=:$paramName";
                                    $this->conditionParams[$paramName] = $value;
                                    break;
                            }
                            break;
                        case 'begins':
                        case 'begins_with':
                            switch ($field->type) {
                                case FIELD::TYPE_TEXT:
                                    if ($field->subtype) {
                                        $this->conditions[$blogic][] = "lower($fieldName) like :$paramName";
                                        $this->conditionParams[$paramName] = strtolower("$value%");
                                    }
                                    else {
                                        $this->conditions[$blogic][] =  "to_tsvector($fieldName) @@ plainto_tsquery(:$paramName)";
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
                                        $this->conditions[$blogic][] = "lower($fieldName) like :$paramName";
                                        $this->conditionParams[$paramName] = strtolower("%$value%");
                                    } else {
                                        $this->conditions[$blogic][] = "to_tsvector($fieldName) @@ plainto_tsquery(:$paramName)";
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
                                        $this->conditions[$blogic][] = "extract(day from $fieldName)=extract('day' from now())";
                                        break;
                                    default:
                                        $this->conditions[$blogic][] = "extract(day from $fieldName)=:$paramName";
                                        $this->conditionParams[$paramName] = $value;
                                        break;
                                }
                            }
                            break;
                        case 'month_is_equal':
                            if ($field->type == FIELD::TYPE_DATE || $field->type == FIELD::TYPE_TIMESTAMP) {
                                switch ($value) {
                                    case '<%current_month%>':
                                        $this->conditions[$blogic][] = "extract(month from $fieldName)=extract('month' from now())";
                                        break;
                                    default:
                                        $this->conditions[$blogic][] = "extract(month from $fieldName)=:$paramName";
                                        $this->conditionParams[$paramName] = $value;
                                        break;
                                }
                            }
                            break;
                        case 'year_is_equal':
                            if ($field->type == FIELD::TYPE_DATE || $field->type == FIELD::TYPE_TIMESTAMP) {
                                switch ($value) {
                                    case '<%current_year%>':
                                        $this->conditions[$blogic][] = "extract(year from $fieldName)=extract('year' from now())";
                                        break;
                                    default:
                                        $this->conditions[$blogic][] = "extract(year from $fieldName)=:$paramName";
                                        $this->conditionParams[$paramName] = $value;
                                        break;
                                }
                            }
                            break;
                        case 'last_x_days':
                            if ($field->type == FIELD::TYPE_DATE || $field->type == FIELD::TYPE_TIMESTAMP && is_numeric($value))
                                $this->conditions[$blogic][] = "$fieldName>=(now()-INTERVAL '$value days')";
                            break;
                        case 'last_x_weeks':
                            if ($field->type == FIELD::TYPE_DATE || $field->type == FIELD::TYPE_TIMESTAMP && is_numeric($value))
                                $this->conditions[$blogic][] = "$fieldName>=(now()-INTERVAL '$value weeks')";
                            break;
                        case 'last_x_months':
                            if ($field->type == FIELD::TYPE_DATE || $field->type == FIELD::TYPE_TIMESTAMP && is_numeric($value))
                                $this->conditions[$blogic][] = "$fieldName>=(now()-INTERVAL '$value months')";
                            break;
                        case 'last_x_years':
                            if ($field->type == FIELD::TYPE_DATE || $field->type == FIELD::TYPE_TIMESTAMP && is_numeric($value))
                                $this->conditions[$blogic][] = "$fieldName>=(now()-INTERVAL '$value years')";
                            break;
                        case 'next_x_days':
                            if ($field->type == FIELD::TYPE_DATE || $field->type == FIELD::TYPE_TIMESTAMP && is_numeric($value))
                                $this->conditions[$blogic][] = "$fieldName>=now() and $fieldName<=(now()+INTERVAL '$value days')";
                            break;
                        case 'next_x_weeks':
                            if ($field->type == FIELD::TYPE_DATE || $field->type == FIELD::TYPE_TIMESTAMP && is_numeric($value))
                                $this->conditions[$blogic][] = "$fieldName>=now() and $fieldName<=(now()+INTERVAL '$value weeks')";
                            break;
                        case 'next_x_months':
                            if ($field->type == FIELD::TYPE_DATE || $field->type == FIELD::TYPE_TIMESTAMP && is_numeric($value))
                                $this->conditions[$blogic][] = "$fieldName>=now() and $fieldName<=(now()+INTERVAL '$value months')";
                            break;
                        case 'next_x_years':
                            if ($field->type == FIELD::TYPE_DATE || $field->type == FIELD::TYPE_TIMESTAMP && is_numeric($value))
                                $this->conditions[$blogic][] = "$fieldName>=now() and $fieldName<=(now()+INTERVAL '$value years')";
                            break;
                    }
                }
            }
        }

        $conditionString = "";
        if (!empty($this->conditions[Where::COMBINED_BY_AND]))
            $conditionString = "(" . implode(" and ", $this->conditions[Where::COMBINED_BY_AND]) . ")";

        if (!empty($this->conditions[Where::COMBINED_BY_OR])) {
            if ($conditionString)
                $conditionString .= " or ";

            $conditionString .= "(" . implode(" or ", $this->conditions[Where::COMBINED_BY_OR]) . ")";
        }

        return $conditionString;
    }

    /**
     * Add conditions for "is_eqaul" operator
     *
     * @param type $field
     * @param type $condition
     */
    private function buildIsEqual($field, $condition, $def)
    {
        $objectTable = $def->getTable();
        $blogic = $condition->bLogic;
        $fieldName = $condition->fieldName;
        $value = $condition->value;

        // Generate the $paramName for this condition and make sure it is unique
        $paramName = $this->generateParamName($fieldName);

        switch ($field->type) {
            case FIELD::TYPE_OBJECT:
                if ($field->subtype) {
                    if ($value) {
                        $this->conditions[$blogic][] = "$fieldName=:$paramName";
                        $this->conditionParams[$paramName] = $value;
                    } else {
                        $this->conditions[$blogic][] = "$fieldName is null";
                    }
                }
                break;
            case FIELD::TYPE_OBJECT_MULTI:
                if (empty($value)) {
                    $this->conditions[$blogic][] = "not EXISTS (select 1 from object_associations
                                        where object_associations.object_id=$objectTable.id
                                        and type_id=:type_id
                                        and field_id=:field_id)";
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

                            $this->conditions[$blogic][] = "EXISTS (select 1 from object_associations
                                    where object_associations.object_id=$objectTable.id
                                    and type_id=:type_id and field_id=:field_id
                                    and assoc_type_id=:$assocTypeParam
                                    and assoc_object_id=:$assocObjParam)";
                        } else // only query associated subtype if there is no referenced id provided
                        {
                            $this->conditions[$blogic][] = "EXISTS (select 1 from object_associations
                                    where object_associations.object_id=$objectTable.id and
                                    type_id=:type_id and field_id=:field_id
                                    and assoc_type_id=:$assocTypeParam)";
                        }
                    }
                }

                $this->conditionParams["type_id"] = $def->getId();
                $this->conditionParams["field_id"] = $field->id;
                break;
            case 'object_dereference':
                // TODO: Ask sky about what is object_dereference
                if ($field->subtype && isset($ref_field)) {

                    // Create subquery
                    /*$subQuery = new EntityQuery($field->subtype);
                    $subQuery->where($ref_field)->equals($value);
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

                if (isset($field->fkeyTable["parent"]) && is_numeric($value)) {
                    $children = $this->getHeiarchyDownGrp($field, $value);
                    foreach ($children as $child) {
                        $childParam = $this->generateParamName($fkeyTableRef);
                        $multiCond[] =  "$fkeyTableRef=:$childParam";
                        $this->conditionParams[$childParam] = $child;
                    }
                } elseif (!empty($value)) {
                    $fkeyRefParam = $this->generateParamName($fkeyTableRef);
                    $multiCond[] = "$fkeyTableRef=:$fkeyRefParam";
                    $this->conditionParams[$fkeyRefParam] = $value;
                }

                $thisfld = $field->fkeyTable['ref_table']["this"];
                $reftbl = $field->fkeyTable['ref_table']['table'];

                if (empty($value)) {
                    $this->conditions[$blogic][] = " NOT EXISTS (select 1 from  $reftbl where $reftbl.$thisfld=$objectTable.id) ";
                } else {
                    $this->conditions[$blogic][] = " EXISTS (select 1 from  $reftbl where $reftbl.$thisfld=$objectTable.id
                            and (" . implode(" or ", $multiCond) . ")) ";
                }
                break;
            case FIELD::TYPE_GROUPING:
                if (empty($value)) {
                    $this->conditions[$blogic][] = "$fieldName is null";
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

                    $this->conditions[$blogic][] = "(" . implode(" or ", $multiCond) . ")";
                }
                break;
            case FIELD::TYPE_TEXT:
                if (empty($value)) {
                    $this->conditions[$blogic][] = "($fieldName is null OR $fieldName='')";
                }
                elseif ($field->subtype) {
                    $this->conditions[$blogic][] = "$fieldName=:$paramName";
                    $this->conditionParams[$paramName] = strtolower($value);
                }
                else {
                    $this->conditions[$blogic][] = "to_tsvector($fieldName) @@ plainto_tsquery(:$paramName)";
                    $this->conditionParams[$paramName] = $value;
                }
                break;
            case FIELD::TYPE_DATE:
            case FIELD::TYPE_TIMESTAMP:
                if ($field->type == FIELD::TYPE_TIMESTAMP)
                    $value = (is_numeric($value)) ? date("Y-m-d H:i:s T", $value) : $value;
                elseif ($field->type == FIELD::TYPE_DATE)
                    $value = (is_numeric($value)) ? date("Y-m-d", $value) : $value;
            default:
                if (!empty($value)) {
                    $this->conditions[$blogic][] = "$fieldName=:$paramName";
                    $this->conditionParams[$paramName] = $value;
                } else {
                    $this->conditions[$blogic][] = "$fieldName is null";
                }
                break;
        }
    }

    /**
     * Add conditions for "is_not_eqaul" operator
     *
     * @param type $field
     * @param type $value
     */
    private function buildIsNotEqual($field, $condition, $def)
    {
        $objectTable = $def->getTable();
        $blogic = $condition->bLogic;
        $fieldName = $condition->fieldName;
        $operator = $condition->operator;
        $value = $condition->value;

        // Generate the $paramName for this condition and make sure it is unique
        $paramName = $this->generateParamName($fieldName);

        switch ($field->type) {
            case FIELD::TYPE_OBJECT:
                // Check if we are querying table directly, otherwise fall through to object_multi code
                if ($field->subtype) {
                    if (empty($value)) {
                        $this->conditions[$blogic][] = "$fieldName is not null";
                    } elseif (isset($field->subtype) && $def->parentField == $fieldName && $value) {
                        $refDef = $this->getDefinition($field->subtype);
                        $refDefTable = $refDef->getTable(true);
                        $parentField = $refDef->parentField;

                        if ($refDef->parentField) {
                            $this->conditions[$blogic][] = "$fieldName not in (WITH RECURSIVE children AS
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
                        $this->conditions[$blogic][] = "$fieldName!=:$paramName";
                        $this->conditionParams[$paramName] = $value;
                    }
                }
                break;
            case FIELD::TYPE_OBJECT_MULTI:

                if (empty($value)) {
                    $this->conditions[$blogic][] = "$objectTable.id in (select object_id from object_associations
                                                    where type_id=:type_id and field_id=:field_id)";
                } else {
                    $objRef = Entity::decodeObjRef($value);

                    if ($objRef) {
                        $refDef = $this->getDefinition($objRef['obj_type']);

                        if ($refDef && $refDef->getId() && !empty($objRef['id'])) {
                            $assocTypeParam = $this->generateParamName("assoc_type_id");
                            $this->conditionParams[$assocTypeParam] = $def->getId();

                            $assocObjParam = $this->generateParamName("assoc_object_id");
                            $this->conditionParams[$assocObjParam] = $objRef['id'];

                            $this->conditions[$blogic][] = "$objectTable.id not in (select object_id from object_associations
                                                        where type_id=:type_id and field_id=:field_id
                                                        and assoc_type_id=:$assocTypeParam and assoc_object_id=:$assocObjParam)";

                        }
                    }
                }

                $this->conditionParams["type_id"] = $def->getId();
                $this->conditionParams["field_id"] = $field->id;
                break;
            case 'object_dereference':
                /*$tmp_cond_str = "";
                if ($field->subtype && $ref_field) {
                    // Create subquery
                    $subQuery = new \Netric\EntityQuery($field->subtype);
                    $subQuery->where($ref_field, $operator, $value);
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

                if (empty($value)) {
                    $this->conditions[$blogic][] = "$objectTable.id in (select $fkeyRefField from $fkeyRefTable)";
                } else {
                    $multiCond = [];

                    if (!empty($field->fkeyTable["parent"]) && is_numeric($value)) {
                        $children = $this->getHeiarchyDownGrp($field, $value);
                        foreach ($children as $child) {
                            $childParam = $this->generateParamName($fkeyTableRef);
                            $multiCond[] =  "$fkeyTableRef=:$childParam";
                            $this->conditionParams[$childParam] = $child;
                        }
                    } else {
                        $fkeyRefParam = $this->generateParamName($fkeyTableRef);
                        $multiCond[] = "$fkeyTableRef=:$fkeyRefParam";
                        $this->conditionParams[$fkeyRefParam] = $value;
                    }

                    $this->conditions[$blogic][] = "$objectTable.id not in (select $fkeyRefField
                                                                  from $fkeyRefTable
                                                                  where " . implode(" or ", $multiCond) . ") ";
                }

                break;
            case FIELD::TYPE_GROUPING:
                if (empty($value)) {
                    $this->conditions[$blogic][] = "$fieldName is not null";
                } else {
                    $multiCond = [];

                    if (!empty($field->fkeyTable["parent"]) && is_numeric($value)) {
                        $children = $this->getHeiarchyDownGrp($field, $value);
                        foreach ($children as $child) {
                            $childParam = $this->generateParamName($fieldName);
                            $multiCond[] =  "$fieldName!=:$childParam";
                            $this->conditionParams[$childParam] = $child;
                        }
                    } else {
                        $multiCond[] = "$fieldName!=$paramName";
                        $this->conditionParams[$paramName] = $value;
                    }

                    $this->conditions[$blogic][] = "((" . implode (" and ", $multiCond) . ")  or $fieldName is null)";
                }

                break;
            case FIELD::TYPE_TEXT:
                if (empty($value)) {
                    $this->conditions[$blogic][] = "($fieldName!='' AND $fieldName is not NULL)";
                } elseif ($field->subtype) {
                    $this->conditions[$blogic][] = "lower($fieldName)!=:$paramName";
                    $this->conditionParams[$paramName] = strtolower($value);
                } else {
                    $this->conditions[$blogic][] = " (to_tsvector($fieldName) @@ plainto_tsquery(:$paramName))='f'";
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
                    $this->conditions[$blogic][] = "($fieldName!=:$paramName or $fieldName is null)";
                    $this->conditionParams[$paramName] = $value;
                } else {
                    $this->conditions[$blogic][] = "$fieldName is not null";
                }
                break;
        }
    }

    /**
     * Get ids of all child entries in a parent-child relationship
     *
     * This function may be over-ridden in specific indexes for performance reasons
     *
     * @param string $table The table to query
     * @param string $parent_field The field containing the id of the parent entry
     * @param int $this_id The id of the child element
     */
    public function getHeiarchyDownGrp(Field $field, $this_id)
    {
        $ret = array();

        // If not heiarchy then just return this
        if (empty($field->fkeyTable["parent"]))
            return array($this_id);

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

        $result = $this->database->query($sql, [$heiarchyIdParam => $this_id]);
        foreach ($result->fetchAll() as $row)
            $ret[] = $row["id"];

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

        if ($data)
            $res->setAggregation($agg->getName(), $data);
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

        if (!$fieldName)
            return false;

        $retData = array();

        $query = "select distinct($fieldName), count($fieldName) as cnt from $objectTable where id is not null";

        if ($conditionQuery)
            $query .= " and $conditionQuery";

        $query .= " GROUP BY $fieldName";
        $result = $this->database->query($sql, $this->conditionParams);

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

        if (!$fieldName)
            return false;

        $query = "select sum($fieldName) as amount from $objectTable where id is not null";

        if ($conditionQuery)
            $query .= " and $conditionQuery";


        $result = $this->database->query($sql, $this->conditionParams);
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

        if (!$fieldName)
            return false;

        $query = "select avg($fieldName) as amount from $objectTable where id is not null";

        if ($conditionQuery)
            $query .= " and $conditionQuery";

        $result = $this->database->query($sql, $this->conditionParams);

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

        if (!$fieldName)
            return false;

        $query = "select min($fieldName) as amount from $objectTable where id is not null";

        if ($conditionQuery)
            $query .= " and $conditionQuery";

        $result = $this->database->query($sql, $this->conditionParams);

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

        if (!$fieldName)
            return false;

        $query = "select "
            . "min($fieldName) as mi, "
            . "max($fieldName) as ma, "
            . "avg($fieldName) as av, "
            . "sum($fieldName) as su "
            . "FROM $objectTable where id is not null ";

        if ($conditionQuery)
            $query .= " and ($conditionQuery) ";

        $result = $this->database->query($sql, $this->conditionParams);
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
