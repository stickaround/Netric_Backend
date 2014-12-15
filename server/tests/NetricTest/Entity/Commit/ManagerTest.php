<?php
/**
 * Test to make sure the commit manager is saving snapshots as expected
 */
namespace NetricTest\Entity\Commit;

use Netric;
use PHPUnit_Framework_TestCase;

class ManagerTest extends PHPUnit_Framework_TestCase 
{
	/**
     * Tennant account
     * 
     * @var \Netric\Account
     */
    private $account = null;

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

	public function testCreateCommit()
	{
		$oCommitManager = $this->account->getServiceManager()->get("EntityCommitManager");
		$lastCommit = $oCommitManager->getHeadCommit($this->entDefCustomer->getId());

		// Create and return a new commit
		$nextCommit = $oCommitManager->createCommit($this->entDefCustomer->getId());

		$this->assertTrue($nextCommit > 0);
		$this->assertNotEquals($lastCommit, $nextCommit);
	}

	public function testGetHeadCommit()
	{
		$oCommitManager = $this->account->getServiceManager()->get("EntityCommitManager");

		// Create and return a new commit
		$nextCommit = $oCommitManager->createCommit($this->entDefCustomer->getId());
		$currentHead = $oCommitManager->getHeadCommit($this->entDefCustomer->getId());

		$this->assertEquals($nextCommit, $currentHead);
	}
}