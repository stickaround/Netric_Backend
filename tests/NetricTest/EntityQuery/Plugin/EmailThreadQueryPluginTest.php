<?php
namespace NetricTest\EntityQuery\Plugin;

use Netric\EntityQuery;
use Netric\EntityQuery\Plugin;
use PHPUnit\Framework\TestCase;
use Netric\WorkerMan;
use Netric\WorkerMan\SchedulerService;

/**
 * @group integration
 */
class EmailThreadQueryPluginTest extends TestCase
{
    /**
     * Tenant account
     *
     * @var \Netric\Account\Account
     */
    protected $account = null;

    /**
     * Setup each test
     */
    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
    }

    public function testOnBeforeQuery()
    {
        // Mock the scheduler service
        $schedulerService = $this->getMockBuilder(SchedulerService::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Setup an in-memory worker queue for testing
        $queue = new WorkerMan\Queue\InMemory();
        $service = new WorkerMan\WorkerService($this->account->getApplication(), $queue, $schedulerService);

        // Create plugin
        $plugin = new Plugin\EmailThreadQueryPlugin();
        $plugin->setWorkerService($service);

        // Setup query and run the plugin just like the index would right before a query
        $query = new EntityQuery("email_thread");
        $query->where("mailbox_id")->equals(123);
        $this->assertTrue($plugin->onBeforeExecuteQuery($this->account->getServiceManager(), $query));

        // Make sure the right job was queued with the right params
        $this->assertEquals(
            array(
                "EmailMailboxSync",
                array(
                    "account_id"=>$this->account->getId(),
                    "user_id"=>$this->account->getUser()->getId(),
                    "mailbox_id"=>123,
                ),
            ),
            $queue->queuedJobs[0]
        );
    }

    public function testOnAfterExecuteQuery()
    {
        $plugin = new Plugin\EmailThreadQueryPlugin();
        $query = new EntityQuery("email_thread");
        $this->assertTrue($plugin->onAfterExecuteQuery($this->account->getServiceManager(), $query));
    }
}