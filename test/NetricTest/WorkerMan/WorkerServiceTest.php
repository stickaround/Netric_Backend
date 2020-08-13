<?php

namespace NetricTest\WorkerMan;

use Netric\WorkerMan\WorkerService;
use Netric\WorkerMan\Queue\InMemory;
use PHPUnit\Framework\TestCase;
use Netric\WorkerMan\WorkerFactory;
use Netric\WorkerMan\Worker\TestWorker;

/**
 * @group integration
 */
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
        $workerFactory = $this->createMock(WorkerFactory::class);
        $queue = new InMemory($workerFactory);

        $this->workerService = new WorkerService($this->account->getApplication(), $queue, $workerFactory);
    }

    public function testDoWorkBackground()
    {
        $this->assertEquals("1", $this->workerService->doWorkBackground(TestWorker::class, ["mystring" => "test"]));
    }

    // public function testProcessJobQueue()
    // {
    //     $this->workerService->doWorkBackground(TestWorker::class, ["mystring" => "test"]);
    //     $this->assertTrue($this->workerService->processJobQueue());
    // }
}
