<?php
/*
 * Interface definition for indexes
 * 
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2014 Aereus
 */
namespace Netric\EntityQuery\Index;

/**
 * Main index interface for DI
 */
interface IndexInterface 
{
    /**
	 * Execute a query and return the results
	 *
     * @param string $id Unique id of object to delete
	 * @return \Netric\EntityQuery\Results
	 */
	public function executeQuery(\Netric\EntityQuery &$query);
}
