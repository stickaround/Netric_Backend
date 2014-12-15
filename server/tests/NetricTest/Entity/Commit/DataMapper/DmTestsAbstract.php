<?php
/**
 * Define common tests that will need to be run with all data mappers.
 *
 * In order to implement the unit tests, a datamapper test case just needs
 * to extend this class and create a getDataMapper class that returns the
 * datamapper to be tested
 *
 * All generic tests should go here, and implementation specific tests 
 * (like querying a database to verify data) should go in the derrived
 * unit tests.
 */
namespace NetricTest\Entity\Commit\DataMapper;

use PHPUnit_Framework_TestCase;

abstract class DmTestsAbstract extends PHPUnit_Framework_TestCase 
{
    /**
     * Tennant account
     * 
     * @var \Netric\Account
     */
    protected $account = null;

    /**
     * Entity definition for customer
     *
     * @var \Netric\Entity\EntityDefinition
     */
    protected $entDefCustomer = null;

	/**
	 * Setup each test
	 */
	protected function setUp() 
	{
        $this->account = \NetricTest\Bootstrap::getAccount();

        $this->entDefCustomer = $this->account->getServiceManager()->get("EntityDefinitionLoader")->get("customer");
	}

	/**
	 * Use this funciton in all the datamappers to construct the datamapper
	 *
	 * @return \Netric\Entity\Commit\DataMaper\DataMapperInterface
	 */
	abstract protected function getDataMapper();

	public function testGetNextCommitId()
	{
		$dm = $this->getDataMapper();
		$nextCid = $dm->getNextCommitId($this->entDefCustomer->getId());
		$this->assertTrue($nextCid > 0);
	}

	public function testSaveHead()
	{
		$dm = $this->getDataMapper();

		// Increment head
		$nextCid = $dm->getNextCommitId($this->entDefCustomer->getId());
		$dm->saveHead($this->entDefCustomer->getId(), $nextCid);

		// Test saved value
		$this->assertEquals($nextCid, $dm->getHead($this->entDefCustomer->getId()));
	}

	public function testGetHead()
	{
		$dm = $this->getDataMapper();

		$currCid = $dm->getHead($this->entDefCustomer->getId());

		// Increment head if new object
		if (0 == $currCid)
		{
			$nextCid = $dm->getNextCommitId($this->entDefCustomer->getId());
			$dm->saveHead($this->entDefCustomer->getId(), $nextCid);
		}

		$currCid = $dm->getHead($this->entDefCustomer->getId());
		$this->assertTrue($currCid > 0);
	}
}