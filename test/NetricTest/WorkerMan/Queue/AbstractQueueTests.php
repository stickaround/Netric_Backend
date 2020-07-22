<?php

namespace NetricTest\WorkerMan\Queue;

use Netric\WorkerMan\Worker\TestWorker;
use Netric\WorkerMan\Queue\QueueInterface;
use Netric\WorkerMan\Queue;
use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
abstract class AbstractQueueTests extends TestCase
{
    /**
     * Reference to account running for unit tests
     *
     * @var \Netric\Account\Account
     */
    protected $account = null;

    /**
     * Action factory for testing
     *
     * @var ActionFactory
     */
    protected $actionFactory = null;

    protected function setUp(): void
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $sl = $this->account->getServiceManager();
    }

    /**
     * Cleanup any leftover tasks in the queue
     */
    protected function tearDown(): void
    {
        $queue = $this->getQueue();
        $queue->clearWorkerQueue("Test");
    }

    /**
     * Construct a job queue
     *
     * @return QueueInterface
     */
    abstract protected function getQueue();

    /*
     * This is hard to test due to threads...
    public function testDoWork()
    {
        // We are in a child account
        $queue = $this->getQueue();

        // Now add a worker which will process the queue
        $worker = new TestWorker();
        $queue->addWorker("Test", $worker);

        // This was causing the test to hang since it could not call the above in the same thread
        $this->assertEquals("tset", $queue->doWork("Test", ["mystring"=>"test"]));
    }
    */

    public function testDoWorkBackground()
    {
        $queue = $this->getQueue();

        // Add a worker which will process the queue
        $worker = new TestWorker($this->account->getApplication());
        $queue->addWorker("Test", $worker);

        // This will queue the job
        $queue->doWorkBackground("Test", ["mystring" => "test"]);

        // Dispatch the job
        $queue->dispatchJobs();

        // Make sure the worker did the work after the fact
        $this->assertEquals("tset", $worker->getResult());
    }

    public function testAddWorker()
    {
        $queue = $this->getQueue();

        // Now add a worker which will process the queue
        $worker = new TestWorker($this->account->getApplication());
        $queue->addWorker("Test", $worker);

        $this->assertEquals(1, count($queue->getWorkers()));
    }

    public function testDispatchJobs()
    {
        $queue = $this->getQueue();

        // This will queue the job
        $queue->doWorkBackground("Test", ["mystring" => "dispatch"]);

        // Now add a worker which will process the queue
        $worker = new TestWorker($this->account->getApplication());
        $queue->addWorker("Test", $worker);

        // Dispatch the job and get the result, it should take the first job on the queue and return
        $this->assertTrue($queue->dispatchJobs());

        // If the job was not run then this could hang forever waiting for a job
    }

    public function testClearWorkerQueue()
    {
        $queue = $this->getQueue();

        // This will queue the job
        $queue->doWorkBackground("Test", ["mystring" => "dispatch"]);
        $queue->doWorkBackground("Test", ["mystring" => "dispatch"]);

        // Add a worker that should never be called
        $worker = new TestWorker($this->account->getApplication());
        $queue->addWorker("Test", $worker);

        // Clear all results
        $this->assertGreaterThanOrEqual(0, $queue->clearWorkerQueue("Test"));

        // Make sure the worker was never called
        $this->assertEmpty($worker->getResult());
    }
}
