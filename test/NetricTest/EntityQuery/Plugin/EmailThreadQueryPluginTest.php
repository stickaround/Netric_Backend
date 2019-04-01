<?php
namespace NetricTest\EntityQuery\Plugin;

use Netric\EntityQuery;
use Netric\EntityQuery\Plugin;
use PHPUnit\Framework\TestCase;
use Netric\WorkerMan;
use Netric\WorkerMan\SchedulerService;
use NetricTest\Bootstrap;
use Netric\EntityDefinition\ObjectTypes;
use Netric\WorkerMan\Queue\InMemory;
use Netric\WorkerMan\WorkerService;
use Netric\EntityQuery\Plugin\EmailThreadQueryPlugin;

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
    protected function setUp(): void
{
        $this->account = Bootstrap::getAccount();
    }

    public function testOnBeforeQuery()
    {
        // Mock the scheduler service
        $schedulerService = $this->getMockBuilder(SchedulerService::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Setup an in-memory worker queue for testing
        $queue = new InMemory();
        $service = new WorkerService($this->account->getApplication(), $queue, $schedulerService);

        // Create plugin
        $plugin = new EmailThreadQueryPlugin();
        $plugin->setWorkerService($service);

        // Setup query and run the plugin just like the index would right before a query
        $query = new EntityQuery(ObjectTypes::EMAIL_THREAD);
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
        $plugin = new EmailThreadQueryPlugin();
        $query = new EntityQuery(ObjectTypes::EMAIL_THREAD);
        $this->assertTrue($plugin->onAfterExecuteQuery($this->account->getServiceManager(), $query));
    }
}
