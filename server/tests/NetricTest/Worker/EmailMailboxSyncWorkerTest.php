<?php
namespace NetricTest\Worker;

use Netric\WorkerMan\Job;
use PHPUnit_Framework_TestCase;
use Netric\Worker\EmailMailboxSyncWorker;
use Netric\Entity\ObjType\EmailAccountEntity;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityGroupings\Group;

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

    /**
     * Test user
     *
     * @var UserEntity
     */
    private $user = null;

    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $sl = $this->account->getServiceManager();

        // Create a test user
        $this->user = $sl->get("EntityLoader")->create("user");
        $this->user->setValue("name", "test_" . rand());
        $sl->get("EntityLoader")->save($this->user);

        // Create an email account for testing
        $config = $sl->get("Config");
        $this->emailAccount = $sl->get("EntityLoader")->create("email_account");
        $this->emailAccount->setValue("owner_id", $this->user->getId());
        $this->emailAccount->setValue("type", "imap");
        $this->emailAccount->setValue("host", $config->imap_host);
        $sl->get("EntityLoader")->save($this->emailAccount);
    }

    protected function tearDown()
    {
        $sl = $this->account->getServiceManager();

        // Cleanup email account
        $sl->get("EntityLoader")->delete($this->emailAccount, true);

        // Cleanup user
        $sl->get("EntityLoader")->delete($this->user, true);
    }

    public function testWork()
    {
        $worker = new EmailMailboxSyncWorker($this->account->getApplication());
        $job = new Job();
        $job->setWorkload([
            "account_id" => $this->account->getId(),
            "user_id" => $this->user->getId(),
            "mailbox_id" => 123
        ]);

        // Make sure it is a success
        $this->assertTrue($worker->work($job));

        // Make sure one account was processed
        $this->assertEquals(1, $job->getStatusDenominator());
    }
}
