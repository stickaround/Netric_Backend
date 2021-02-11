<?php

declare(strict_types=1);

namespace Netric\EntityQuery;

use InvalidArgumentException;
use Netric\Entity\DataMapper\EntityDataMapperInterface;
use Netric\EntityQuery\Aggregation\AggregationInterface;
use Netric\Entity\EntityInterface;

/**
 * Build an an entity query
 */
class EntityQuery
{
    /**
     * The object type we are working with
     *
     * @var string
     */
    private string $objType = "";

    /**
     * Account we will query entities for
     *
     * @var string
     */
    private string $accountId = "";

    /**
     * Optional. The unique id of the user that is executing this entity query.
     * If provided, this will be used to sanitize current user in condition value
     *
     * @var string
     */
    private string $userId = "";

    /**
     * Array of where conditions
     *
     * @var array [['blogic', 'field', 'operator', 'value']]
     */
    private array $wheres = [];

    /**
     * Order by fields
     *
     * @var array [['field', 'direction']]
     */
    private array $orderBy = [];

    /**
     * DataMapper reference used for automatic pagination after initial load
     *
     * @var EntityDataMapperInterface
     */
    private EntityDataMapperInterface $dataMapper;

    /**
     * Array of entities that are loaded in this collection
     *
     * @param Entity[]
     */
    private array $entities = [];

    /**
     * Limit number of entities loaded from datamapper per page
     *
     * @var int
     */
    private int $limitPerPage = 100;

    /**
     * The current offset
     *
     * @var int
     */
    private int $offset = 0;

    /**
     * The starting offset of the next page
     *
     * This is set by the datamapper when the query is done
     *
     * @var int
     */
    private int $nextPageOffset = -1;

    /**
     * The starting offset of the previous page
     *
     * This is set by the datamapper when the query is done
     *
     * @var int
     */
    private int $prevPageOffset = -1;

    /**
     * Total number of entities in the collection
     *
     * @var int
     */
    private int $totalNum = 0;

    /**
     * Aggregations to use with this query
     *
     * @var AggregationInterface[]
     */
    private array $aggregations = [];

    /**
     * Operator constants
     *
     * @var const string
     */
    const OP_EQUALTO = "is_equal";
    const OP_DOESNOTEQUAL = "is_not_equal";

    /**
     * Class constructor
     *
     * @param string $objType Unique name of the object type we are querying
     * @param string $accountId The account we are going to query entities for
     * @param string $userId Optional. The id of the user that is executing this entity query.
     */
    public function __construct(string $objType, string $accountId, string $userId = '')
    {
        if (empty($accountId)) {
            throw new InvalidArgumentException('accountId cannot be empty');
        }

        $this->objType = $objType;
        $this->accountId = $accountId;
        $this->userId = $userId;
    }

    /**
     * Get the object type for this collection
     *
     * @return string
     */
    public function getObjType(): string
    {
        return $this->objType;
    }

    /**
     * Get the account we are querying for
     *
     * @return string Unique ID of the account to query entities for
     */
    public function getAccountId(): string
    {
        return $this->accountId;
    }

    /**
     * Get the userId that is executing the entity query
     *
     * @return string Unique ID of the user executing this query
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * Add a where condition
     *
     * @param string $fieldName
     * @param string $operator Optional constructor operator
     * @param mixed $value The condition value
     * @return Where
     */
    public function where(string $fieldName, string $operator = "", string $value = "")
    {
        $where = new Where($fieldName);
        if ($operator) {
            $where->operator = $operator;
        }
        $where->value = $value;
        $this->addCondition($where);
        return $where;
    }

    /**
     * Add a where condition with "and" boolean logic
     *
     * @param string $fieldName
     * @return Where
     */
    public function andWhere(string $fieldName, string $operator = "", string $value = "")
    {
        return $this->where($fieldName, $operator, $value);
    }

    /**
     * Add a where condition with 'or' blogic
     *
     * @param string $fieldName
     * @return Where
     */
    public function orWhere($fieldName, $operator = "", $value = "")
    {
        $where = $this->where($fieldName, $operator, $value);
        $where->bLogic = "or";

        return $where;
    }

    /**
     * Add where condition with where object
     *
     * @param Where $where
     */
    private function addCondition(Where $where)
    {
        $this->wheres[] = $where;
    }

    /**
     * Get array of wheres used to filter this collection
     *
     * @return Netric\EntityQuery\Where[]
     */
    public function getWheres()
    {
        return $this->wheres;
    }

    /**
     * Check if a field exists in the wheres array
     *
     * @param string $fieldName
     * @return bool
     */
    public function fieldIsInWheres(string $fieldName): bool
    {
        foreach ($this->getWheres() as $where) {
            if ($where->fieldName === $fieldName) {
                return true;
            }
        }
        return false;
    }

    /**
     * Add a field to order this by
     *
     * @param string $fieldName
     * @param string $direction
     * @return Netric/EntityQuery
     */
    public function orderBy($fieldName, $direction = OrderBy::ASCENDING)
    {
        $this->addOrderBy(new OrderBy($fieldName, $direction));
        return $this;
    }

    /**
     * Private function for adding an order by object to this query
     *
     * @param OrderBy $orderBy
     */
    private function addOrderBy(OrderBy $orderBy)
    {
        $this->orderBy[] = $orderBy;
    }

    /**
     * Get array of order by used to filter this collection
     *
     * @return array(array("field", "direction"))
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     *  Set local reference to datamapper for loading objects and auto pagination
     *
     * @param EntityDataMapperInterface $dataMapper
     */
    public function setDataMapper(EntityDataMapperInterface $dataMapper)
    {
        $this->dataMapper = $dataMapper;
    }

    /**
     * Restrict the number of entities that can be loaded per page
     *
     * @param int $num Number of items to load per page
     */
    public function setLimit($num)
    {
        $this->limitPerPage = $num;
    }

    /**
     * Get the limit per page that can be loaded
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->limitPerPage;
    }

    /**
     * Determine if this query is searching for deleted items or active
     *
     * @return boolean True if we are looking for deleted items
     */
    public function isDeletedQuery()
    {
        $ret = false;
        $wheres = $this->getWheres();
        foreach ($wheres as $where) {
            if ("f_deleted" == $where->field && true == $where->value) {
                $ret = true;
                break;
            }
        }
        return $ret;
    }

    /**
     * Get the offset of the next page for automatic pagination
     *
     * @return int $offset
     */
    public function getNextPageOffset()
    {
        return $this->nextPageOffset;
    }

    /**
     * Get the offset of the previous page for automatic pagination
     *
     * @return int $offset
     */
    public function getPrevPageOffset()
    {
        return $this->prevPageOffset;
    }

    /**
     * Set the offset
     *
     * @param int $offset
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }

    /**
     * Get current offset
     *
     * @return int $offset
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Set the total number of entities for the defined query
     *
     * The collection will load one page at a time
     *
     * @param int $num The total number of entities in this query collection
     */
    public function setTotalNum($num)
    {
        $this->totalNum = $num;
    }

    /**
     * Get the total number of entities in this collection
     *
     * @return int Total number of entities
     */
    public function getTotalNum()
    {
        return $this->totalNum;
    }

    /**
     * Add an entity to this collection
     *
     * @param EntityInterface $entity
     */
    public function addEntity(EntityInterface $entity)
    {
        $this->entities[] = $entity;
    }

    /**
     * Reset the entities array
     */
    public function clearEntities()
    {
        $this->entities = [];
    }
    /**
     * Add aggregation to this query
     *
     * @param AggregationInterface
     */
    public function addAggregation(AggregationInterface $agg)
    {
        $this->aggregations[] = $agg;
    }

    /**
     * Get aggregations for this query
     *
     * @return AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * Check if this query has any aggregations
     *
     * @return bool true if aggs exist, otherwise false
     */
    public function hasAggregations()
    {
        return (count($this->aggregations) > 0) ? true : false;
    }

    /**
     * Execute the query for this collection
     *
     * @return boolean|int Number of entities loaded if success and datamapper is set, false on failure
     */
    public function load()
    {
        if ($this->dataMapper) {
            return $this->dataMapper->loadCollection($this);
        } else {
            return false;
        }
    }

    /**
     * Convert this query to an array
     *
     * @return array ('conditions'=>Wheres[]->toArray, 'order_by'=>
     */
    public function toArray()
    {
        $ret = [
            "obj_type" => $this->objType,
            "limit" => $this->limitPerPage,
            "offset" => $this->offset,
        ];

        // Add all where conditions
        $ret['conditions'] = [];
        $wheres = $this->getWheres();
        foreach ($wheres as $whereCondition) {
            $ret['conditions'][] = $whereCondition->toArray();
        }

        // Add order by
        $ret['order_by'] = [];
        $orderBy = $this->getOrderBy();
        foreach ($orderBy as $sortDef) {
            $ret['order_by'][] = $sortDef->toArray();
        }

        return $ret;
    }

    /**
     * Load in a query from an array
     *
     * @param array $data The query to load
     * @throws \InvalidArgumentException if the data query is invalid
     */
    public function fromArray(array $data)
    {
        // Basic level validation
        if (!isset($data['obj_type'])) {
            throw new \InvalidArgumentException("obj_type is a required query index");
        }

        $this->objType = $data['obj_type'];

        if (isset($data['limit'])) {
            $this->setLimit($data['limit']);
        }

        if (isset($data['offset'])) {
            $this->setOffset($data['offset']);
        }

        // Add conditions if they were passed
        if (isset($data['conditions']) && is_array($data['conditions'])) {
            foreach ($data['conditions'] as $condData) {
                $where = new Where();
                $where->fromArray($condData);
                $this->addCondition($where);
            }
        }

        // Add order_by if they were passed
        if (isset($data['order_by']) && is_array($data['order_by'])) {
            foreach ($data['order_by'] as $sortData) {
                $order = new OrderBy();
                $order->fromArray($sortData);
                $this->addOrderBy($order);
            }
        }
    }
}
