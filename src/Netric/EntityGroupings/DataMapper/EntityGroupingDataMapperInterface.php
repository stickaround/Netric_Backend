<?php
namespace Netric\EntityGroupings\DataMapper;

use Netric\EntityGroupings\EntityGroupings;

/**
 * A DataMapper is responsible for writing and reading grouping data to a database
 */
interface EntityGroupingDataMapperInterface
{
    /**
     * Get object groupings based on unique path
     *
     * @param string $path The path of the object groupings that we are going to query
     * @return EntityGroupings
     */
    public function getGroupings(string $path) : EntityGroupings;

    /**
     * Save groupings
     *
     * @param EntityGroupings $groupings Groupings object to save
     * @return array("changed"=>int[], "deleted"=>int[]) Log of changed groupings
     */
    public function saveGroupings(EntityGroupings $groupings) : array;
}
