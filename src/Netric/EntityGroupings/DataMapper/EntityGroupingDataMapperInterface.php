<?php
namespace Netric\EntityGroupings\DataMapper;

use Netric\EntityGroupings\EntityGroupings;

/**
 * A DataMapper is responsible for writing and reading grouping data to a database
 */
interface EntityGroupingDataMapperInterface
{
    /**
     * Get object definition based on an object type
     *
     * @param string $objType The object type name
     * @param string $fieldName The field name to get grouping data for
     * @param int $userId Optional. Used to load a private grouping
     * @return EntityGroupings
     */
    public function getGroupings(string $objType, string $fieldName, int $userId = null) : EntityGroupings;

    /**
     * Save groupings
     *
     * @param EntityGroupings $groupings Groupings object to save
     * @return array("changed"=>int[], "deleted"=>int[]) Log of changed groupings
     */
    public function saveGroupings(EntityGroupings $groupings) : array;
}
