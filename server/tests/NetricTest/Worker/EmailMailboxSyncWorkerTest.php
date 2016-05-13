<?php
namespace NetricTest\Worker;

use Netric\WorkerMan\Job;
use PHPUnit_Framework_TestCase;
use Netric\Worker\EmailMailboxSyncWorker;
use Netric\Entity\ObjType\EmailAccountEntity;

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

    /**
     * Email account
     *
     * @var EmailAccountEntity
     */
    private $emailAccount = null;

    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $sl = $this->account->getServiceManager();

        // Create an email account for testing
        $this->emailAccount = $sl->get("EntityLoader")->create("email_account");
        $this->emailAccount->setValue("owner_id", $this->account->getUser()->getId());
        $this->emailAccount->setValue("type", "imap");
        $sl->get("EntityLoader")->save($this->emailAccount);
    }

    protected function tearDown()
    {
        $sl = $this->account->getServiceManager();

        // Cleanup email account
        $sl->get("EntityLoader")->delete($this->emailAccount, true);
    }

    public function testWork()
    {
        $worker = new EmailMailboxSyncWorker($this->account->getApplication());
        $job = new Job();
        $job->setWorkload([
            "account_id" => $this->account->getId(),
            "user_id" => $this->account->getUser()->getId(),
            "mailbox_id" => 123
        ]);

        // Make sure it is a success
        $this->assertTrue($worker->work($job));

        // Make sure one account was processed
        $this->assertEquals(1, $job->getStatusDenominator());
    }
}
