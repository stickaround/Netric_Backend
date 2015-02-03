<?php
/**
 * Test entity/object class
 */
namespace NetricTest\EntitySync\Collection;

use Netric\EntitySync\Collection;
use PHPUnit_Framework_TestCase;

class EntityCollectionTest extends AbstractCollectionTests 
{
	protected function getCollection()
	{
		$dm = $this->account->getServiceManager()->get("EntitySync_DataMapper");
		$index = $this->account->getServiceManager()->get("EntityQuery_Index");
		return new Collection\EntityCollection($dm, $index);
	}

	/**
	 * Make sure we can construct this colleciton
	 */
	public function testConstruct()
	{
		$coll = $this->getCollection();
		
		$this->assertInstanceOf('\Netric\EntitySync\Collection\CollectionInterface', $coll);
	}

}
