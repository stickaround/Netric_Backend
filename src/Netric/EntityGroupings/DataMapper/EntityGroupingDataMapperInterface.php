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
     * @param array $filters Used to load a subset of groupings (like just for a specific user)
     * @return EntityGroupings
     */
    public function getGroupings(string $objType, string $fieldName, array $filters = []);

    /**
     * Save groupings
     *
     * @param EntityGroupings $groupings Groupings object to save
     * @param int $commitId The commit id of this save
     * @return array("changed"=>int[], "deleted"=>int[]) Log of changed groupings
     */
    public function saveGroupings(EntityGroupings $groupings, int $commitId);
}
