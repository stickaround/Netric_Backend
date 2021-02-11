<?php

namespace Netric\EntityQuery;

use Netric\Stats\StatsPublisher;
use Netric\Entity\EntityInterface;
use Netric\EntityQuery\EntityQuery;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\Models\Entity;

/**
 * Results of entity query will be managed here
 */
class Results
{
    /**
     * The query used to construct these results
     *
     * @var EntityQuery
     */
    private EntityQuery $query;

    /**
     * DataMapper reference used for automatic pagination after initial load
     *
     * @var IndexInterface
     */
    private ?IndexInterface $index = null;

    /**
     * Array of entities that are loaded in this collection
     *
     * @param EntityInterface
     */
    private array $entities = [];

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
     * Aggregation data
     *
     * @var array("name"=>array(data))
     */
    private array $aggregations = [];

    /**
     * Class constructor
     *
     * @param string $objType Unique name of the object type we are querying
     */
    public function __construct(EntityQuery $query, IndexInterface &$index = null)
    {
        $this->query = $query;
        if ($index) {
            $this->index = $index;
        }
    }

    /**
     * Get the object type for this collection
     *
     * @return string
     */
    public function getObjType(): string
    {
        return $this->query->getObjType();
    }

    /**
     *  Set local reference to datamapper for loading objects and auto pagination
     *
     * @param IndexInterface &$index
     */
    public function setIndex(IndexInterface $index)
    {
        $this->index = $index;
    }

    /**
     * Get the offset of the next page for automatic pagination
     *
     * @return int $offset
     */
    public function getNextPageOffset(): int
    {
        return $this->nextPageOffset;
    }

    /**
     * Get the offset of the previous page for automatic pagination
     *
     * @return int $offset
     */
    public function getPrevPageOffset(): int
    {
        return $this->prevPageOffset;
    }

    /**
     * Set the offset
     *
     * @param int $offset
     */
    public function setOffset(int $offset)
    {
        $this->query->setOffset($offset);
    }

    /**
     * Get current offset
     *
     * @return int $offset
     */
    public function getOffset(): int
    {
        return $this->query->getOffset();
    }

    /**
     * Set the total number of entities for the defined query
     *
     * The collection will load one page at a time
     *
     * @param int $num The total number of entities in this query collection
     */
    public function setTotalNum(int $num)
    {
        $this->totalNum = $num;
    }

    /**
     * Get the total number of entities in this collection
     *
     * @return int Total number of entities
     */
    public function getTotalNum(): int
    {
        return $this->totalNum;
    }

    /**
     * Get the number of entities in the current loaded page
     *
     * $return int Number of entities in the current page
     */
    public function getNum(): int
    {
        return count($this->entities);
    }

    /**
     * Add an entity to this collection
     *
     * @param EntityInterface $entity
     */
    public function addEntity(EntityInterface $entity)
    {
        // Stat a cache list hit
        StatsPublisher::increment("entity.cache.queryres");
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
     * Retrieve an entity from the collection
     *
     * @param int $offset The offset of the entity to get in the collection
     * @return EntityInterface
     */
    public function getEntity(int $offset = 0)
    {
        if ($offset >= ($this->getOffset() + $this->query->getLimit()) ||
            $offset < $this->getOffset()
        ) {
            // Get total number of pages
            $leftover = $this->totalNum % $this->query->getLimit();
            if ($leftover) {
                $numpages = (($this->totalNum - $leftover) / $this->query->getLimit()) + 1;
            } else {
                $numpages = $this->totalNum / $this->query->getLimit();
            }

            // Get current page offset
            $page = floor($offset / $this->query->getLimit());
            if ($page) {
                $this->setOffset(round($page * $this->query->getLimit(), 0));
            } else {
                $this->setOffset(0);
            }


            // Automatially load the next page
            if ($this->index) {
                $this->index->executeQuery($this->query, $this);
            }
        }

        // Adjust offset for pagination
        $offset = $offset - $this->getOffset();

        // Make sure the user has not requested a bad offset
        if ($offset >= count($this->entities)) {
            throw new \RuntimeException(
                "getEntity at index $offset is beyond the bounds of this page" .
                    "totalNum:" . $this->totalNum . ', ' .
                    "numLoadedEntities: " . count($this->entities) . ', ' .
                    "resultsOffset:" . $this->getOffset() . ', ' .
                    "queryLimit:" . $this->query->getLimit()
            );
        }

        return $this->entities[$offset];
    }

    /**
     * Set aggregation data
     *
     * @param string $name The unique name of this aggregation
     * @param int|string|array $value
     */
    public function setAggregation(string $name, $value)
    {
        $this->aggregations[$name] = $value;
    }

    /**
     * Get aggregation data for this query by name
     *
     * @return []
     */
    public function getAggregation(string $name)
    {
        if (isset($this->aggregations[$name])) {
            return $this->aggregations[$name];
        }

        return false;
    }

    /**
     * Get aggregations data for this query
     *
     * @return array("name"=>array(data))
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
    public function hasAggregations(): bool
    {
        return (count($this->aggregations) > 0) ? true : false;
    }
}
