<?php
/**
 * ElasticSearch implementation of indexer for querying objects
 *
 * @author      Sky Stebnicki, sky.stebnicki@aereus.com
 * @copyright   Copyright (c) 2003-2014 Aereus (http://www.aereus.com)
 */
namespace Netric\EntityQuery\Index;

use Netric\EntityQuery;
use Netric\EntityQuery\Results;
use Netric\Account\Account;
use Netric\Entity\Entity;

class ElasticSearch extends IndexAbstract
{
    /**
     * Setup this index for the given account
     *
     * @param Account $account
     */
    protected function setUp(Account $account)
    {
    }
    
    /**
     * Save an object to the index
     *
     * @param Entity $entity Entity to save
     * @return bool true on success, false on failure
     */
    public function save(Entity $entity)
    {
        // TODO: build
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
        // TODO: build
        return true;
    }

    /**
     * Execute a query and return the results
     *
     * @param EntityQuery &$query The query to execute
     * @param Results $results Optional results set to use. Otherwise create new.
     * @return \Netric\EntityQuery\Results
     */
    protected function queryIndex(EntityQuery $query, Results $results = null)
    {
        return null;
    }

    /**
     * Execute a query and return the results
     *
     * @param EntityQuery $query A query to execute
     * @param Results $results Optional results set to use. Otherwise create new.
     * @return \Netric\EntityQuery\Results
     */
    public function executeQuery(EntityQuery $query, Results $results = null)
    {
        return false;
    }
}
