<?php
namespace NetricTest\Worker;

use Netric\WorkerMan\Job;
use PHPUnit_Framework_TestCase;
use Netric\Worker\EmailMailboxSyncWorker;

/**
 * @group integration
 */
class EmailMailboxSyncWorkerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Reference to account running for unit tests
     *
     * @var \Netric\Account\Account
     */
    private $account = null;

    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $sl = $this->account->getServiceManager();
    }

    public function testWork()
    {
        $worker = new EmailMailboxSyncWorker($this->account->getApplication());
        $job = new Job();
        $job->setWorkload([
            "account_id" => $this->account->getId(),
            "user_id" => 123,
            "mailbox_id" => 123
        ]);

        $this->assertTrue($worker->work($job));
    }
}
