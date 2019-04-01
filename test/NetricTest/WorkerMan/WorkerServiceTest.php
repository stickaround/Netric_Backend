<?php
namespace NetricTest\WorkerMan;

use Netric\WorkerMan\WorkerService;
use Netric\WorkerMan\Queue;
use PHPUnit\Framework\TestCase;
use Netric\WorkerMan\SchedulerService;

class WorkerServicetest extends TestCase
{
    /**
     * Reference to account running for unit tests
     *
     * @var \Netric\Account\Account
     */
    private $account = null;

    /**
     * Action factory for testing
     *
     * @var ActionFactory
     */
    protected $actionFactory = null;

    /**
     * Test instance of a worker service with mocked dependencies
     *
     * @var WorkerService
     */
    private $workerService = null;

    protected function setUp(): void
{
        $this->account = \NetricTest\Bootstrap::getAccount();
        $sl = $this->account->getServiceManager();

        $queue = new Queue\InMemory();

        $this->workerService = new WorkerService($this->account->getApplication(), $queue);
    }

    public function testDoWork()
    {
        $this->assertTrue($this->workerService->doWork("Test", array("mystring"=>"test")));
    }

    public function testDoWorkBackground()
    {
        $this->assertEquals("1", $this->workerService ->doWorkBackground("Test", array("mystring"=>"test")));
    }

    public function testProcessJobQueue()
    {
        $this->workerService->doWorkBackground("Test", array("mystring"=>"test"));
        $this->assertTrue($this->workerService->processJobQueue());
    }
}
