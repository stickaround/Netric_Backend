<?php

namespace NetricTest\Handler;

use PHPUnit\Framework\TestCase;
use Netric\Account\Account;
use Netric\Handler\WorkerHandler;
use Netric\WorkerMan\WorkerService;

/**
 * Test the worker handler
 */
class WorkerHandlerTest extends TestCase
{
    /**
     * Initialized Handler with mock dependencies
     */
    private WorkerHandler $workerHandler;

    /**
     * Mock service for sending jobs
     *
     * @var WorkerService
     */
    private WorkerService $mockWorkerService;

    protected function setUp(): void
    {
        // Mock worker serivce
        $this->mockWorkerService = $this->createMock(WorkerService::class);

        // Create the controller with mocks
        $this->workerHandler = new WorkerHandler(
            $this->mockWorkerService
        );
    }

    /**
     * Test sending an existing job
     */
    public function testProcess()
    {
        $testData = ['var' => 'val'];

        // Mock isValid
        $this->mockWorkerService
            ->expects($this->once())
            ->method('processJob')
            ->with(
                $this->equalTo('Test\Worker'),
                $this->equalTo($testData)
            )->willReturn(true);

        // Test
        $this->assertTrue($this->workerHandler->process('Test\Worker', json_encode($testData)));
    }
}
