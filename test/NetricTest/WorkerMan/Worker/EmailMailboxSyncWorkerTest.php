<?php
namespace NetricTest\WorkerMan\Worker;

use Netric\WorkerMan\Job;
use PHPUnit\Framework\TestCase;
use Netric\WorkerMan\Worker\EmailMailboxSyncWorker;
use Netric\Entity\ObjType\EmailAccountEntity;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityGroupings\Group;
use Netric\Entity\EntityLoaderFactory;
use NetricTest\Bootstrap;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Config\ConfigFactory;

/**
 * @group integration
 */
class EmailMailboxSyncWorkerTest extends TestCase
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

    protected function setUp(): void
{
        $this->account = \NetricTest\Bootstrap::getAccount();
        $sl = $this->account->getServiceManager();

        // Create a test user
        $this->user = $sl->get(EntityLoaderFactory::class)->create(ObjectTypes::USER);
        $this->user->setValue("name", "test_" . rand());
        $sl->get(EntityLoaderFactory::class)->save($this->user);

        // Create an email account for testing
        $config = $sl->get(ConfigFactory::class);
        $this->emailAccount = $sl->get(EntityLoaderFactory::class)->create(ObjectTypes::EMAIL_ACCOUNT);
        $this->emailAccount->setValue("owner_id", $this->user->getId());
        $this->emailAccount->setValue("type", "imap");
        $this->emailAccount->setValue("host", $config->imap_host);
        $sl->get(EntityLoaderFactory::class)->save($this->emailAccount);
    }

    protected function tearDown(): void
{
        $sl = $this->account->getServiceManager();

        // Cleanup email account
        $sl->get(EntityLoaderFactory::class)->delete($this->emailAccount, true);

        // Cleanup user
        $sl->get(EntityLoaderFactory::class)->delete($this->user, true);
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

    /**
     * Makae sure that only one worker processes an account at the same time
     */
    public function testWorkConcurrent()
    {
        $worker = new EmailMailboxSyncWorker($this->account->getApplication());
        $job = new Job();
        $job->setWorkload([
            "account_id" => $this->account->getId(),
            "user_id" => $this->user->getId(),
            "mailbox_id" => 123
        ]);
        
        // Set working flag
        $this->emailAccount->setValue("f_synchronizing", true);
        $sl = $this->account->getServiceManager();
        $sl->get(EntityLoaderFactory::class)->save($this->emailAccount);

        // Make sure it is a success
        $this->assertTrue($worker->work($job));

        // Make sure we skipped the account since it is already processing
        $this->assertEquals(0, $job->getStatusDenominator());
    }

    /**
     * Make sure that only one worker processes an account at the same time
     */
    public function testWorkConcurrentExpired()
    {
        $worker = new EmailMailboxSyncWorker($this->account->getApplication());
        $job = new Job();
        $job->setWorkload([
            "account_id" => $this->account->getId(),
            "user_id" => $this->user->getId(),
            "mailbox_id" => 123
        ]);

        // Set working flag, but a long time ago which will force it to run again (simulating a failure)
        $this->emailAccount->setValue("f_synchronizing", true);
        $this->emailAccount->setValue("ts_last_full_sync", strtotime("-1 day"));

        $sl = $this->account->getServiceManager();
        $sl->get(EntityLoaderFactory::class)->save($this->emailAccount);

        // Make sure it is a success
        $this->assertTrue($worker->work($job));

        // Make sure one account was processed even though the f_synchronizing flag was set
        $this->assertEquals(1, $job->getStatusDenominator());
    }
}
