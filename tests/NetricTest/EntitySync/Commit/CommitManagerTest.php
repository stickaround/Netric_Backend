<?php
/**
 * Test to make sure the commit manager is saving snapshots as expected
 */
namespace NetricTest\EntitySync\Commit;

use Netric;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\EntitySync\Commit\CommitManagerFactory;

class CommitManagerTest extends TestCase
{
    /**
     * Tennant account
     *
     * @var \Netric\Account\Account
     */
    private $account = null;

    /**
     * Setup each test
     */
    protected function setUp()
    {
        $this->account = Bootstrap::getAccount();
    }

    public function testCreateCommit()
    {
        $oCommitManager = $this->account->getServiceManager()->get(CommitManagerFactory::class);

        $lastCommit = $oCommitManager->getHeadCommit("test");

        // Create and return a new commit
        $nextCommit = $oCommitManager->createCommit("test");

        $this->assertTrue($nextCommit > 0);
        $this->assertNotEquals($lastCommit, $nextCommit);
    }

    public function testGetHeadCommit()
    {
        $oCommitManager = $this->account->getServiceManager()->get(CommitManagerFactory::class);

        // Create and return a new commit
        $nextCommit = $oCommitManager->createCommit("test");
        $currentHead = $oCommitManager->getHeadCommit("test");

        $this->assertEquals($nextCommit, $currentHead);
    }
}
