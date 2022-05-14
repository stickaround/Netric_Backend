<?php

namespace NetricTest\WorkerMan\Worker;

use Netric\WorkerMan\Job;
use PHPUnit\Framework\TestCase;
use Netric\WorkerMan\Worker\ScheduleRunnerWorker;
use Netric\WorkerMan\WorkerService;
use Netric\Account\Account;
use NetricTest\Bootstrap;
use Netric\Account\AccountContainerInterface;
use Netric\Log\LogInterface;
use Netric\WorkerMan\Worker\CronMinutelyWorker;
use Netric\WorkerMan\WorkerServiceInterface;

/**
 * Make sure that the scheudle runner will queue jobs
 *
 * @group integration
 */
class CronMinutelyWorkerTest extends TestCase
{
    /**
     * Reference to account running for unit tests
     *
     * @var Account
     */
    private $account = null;

    /**
     * Worker instance to test
     *
     * @var ScheduleRunnerWorker
     */
    private $worker = null;

    /**
     * Mock worker service to interact with
     *
     * @var WorkerService
     */
    private $workerService = null;

    /**
     * Mock contaner for getting accounts
     *
     * @var AccountContainerInterface
     */
    private AccountContainerInterface $mockAccountContainer;

    /**
     * Setup the worker
     */
    protected function setUp(): void
    {
        $this->account = Bootstrap::getAccount();
        // Create a mock worker service
        $this->workerService = $this->getMockBuilder(WorkerServiceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Create account container stub for returning the test account
        $this->mockAccountContainer = $this->createStub(AccountContainerInterface::class);
        $this->mockAccountContainer->method('getAllActiveAccounts')->willReturn([
            ['account_id' => $this->account->getAccountId()]
        ]);
        $this->mockAccountContainer->method('loadById')->willReturn($this->account);

        $log = $this->createStub(LogInterface::class);

        // Worker to test
        $this->worker = new CronMinutelyWorker(
            $this->mockAccountContainer,
            $this->workerService,
            $log
        );
    }

    /**
     * Test the main work function of the worker
     *
     * @return void
     */
    public function testWork()
    {
        $job = new Job();

        // Make sure it is a success
        $this->assertTrue($this->worker->work($job));
    }
}
