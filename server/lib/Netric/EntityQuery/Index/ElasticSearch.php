<?php
/**
 * ElasticSearch implementation of indexer for querying objects
 *
 * @author		Sky Stebnicki, sky.stebnicki@aereus.com
 * @copyright	Copyright (c) 2003-2014 Aereus (http://www.aereus.com)
 */
namespace Netric\EntityQuery\Index;

class ElasticSearch extends IndexAbstract
{
    /**
     * Setup this index for the given account
     * 
     * @param \Netric\Account $account
     */
    protected function setUp(\Netric\Account $account)
    {
        
    }
    
    /**
	 * Save an object to the index
	 *
     * @param \Netric\Entity $entity Entity to save
	 * @return bool true on success, false on failure
	 */
	public function save(\Netric\Entity $entity)
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
     * @param string $id Unique id of object to delete
	 * @return \Netric\EntityQuery\Results
	 */
	public function executeQuery(\Netric\EntityQuery &$query)
    {
        return false;
    }
}