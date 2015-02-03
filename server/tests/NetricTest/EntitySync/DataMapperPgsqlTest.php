<?php
/**
 * Test entity definition loader class that is responsible for creating and initializing exisiting definitions
 */
namespace NetricTest\EntitySync;

use Netric\EntitySync;

class DataMapperPgsqlTest extends AbstractDataMapperTests 
{
	/**
	 * Setup datamapper for the parent DataMapperTests class
	 *
	 * @return DataMapperInterface
	 */
	protected function getDataMapper()
	{
		$dbh = $this->account->getServiceManager()->get("Db");
		return new EntitySync\DataMapperPgsql($this->account, $dbh);
	}

	/**
	 * Test construction
	 */
	public function testConstruct()
	{
		$dm = $this->getDataMapper();
		$this->assertInstanceOf('\Netric\EntitySync\DataMapperPgsql', $dm);
	}
	
}
