<?php
namespace NetricTest;

use PHPUnit_Framework_TestCase;
use Netric;

class EntityQueryTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test addition a filter condition to a collection
     */
    public function testWhere()
    {
        $collection = new Netric\EntityQuery("customer");        
        $collection->where('name')->equals("test");
        //$collection->orWhere('fieldname')->isGreaterThan("value");
        //$collection->andWhere('fieldname')->isLessThan("value");
        
        // Get the protected and private values
		$refColl = new \ReflectionObject($collection);
		$wheresProp = $refColl->getProperty('wheres');
		$wheresProp->setAccessible(true);

		// Test values
        $wheres = $wheresProp->getValue($collection);
		$this->assertEquals("name", $wheres[0]->fieldName, "Where name not set");
		$this->assertEquals("test", $wheres[0]->value, "Where condtiion value not set");
    }
    
    /**
     * Test addition an order by condition to a collection
     */
    public function testOrderBy()
    {
        $collection = new Netric\EntityQuery("customer");        
        $collection->orderBy("name");
        
        // Get the protected and private values
		$refColl = new \ReflectionObject($collection);
		$orderByProp = $refColl->getProperty('orderBy');
		$orderByProp->setAccessible(true);

		// Test values
        $orderBy = $orderByProp->getValue($collection);
		$this->assertEquals("name", $orderBy[0]['field'], "Order by name not set");
    }
}