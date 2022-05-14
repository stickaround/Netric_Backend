<?php

namespace NetricTest\WorkerMan\Worker;

use Netric\WorkerMan\Job;
use PHPUnit\Framework\TestCase;
use Netric\WorkerMan\Worker\ScheduleRunnerWorker;
use Netric\WorkerMan\WorkerService;
use Netric\Account\Account;
use NetricTest\Bootstrap;
use Netric\Account\AccountContainerInterface;
use Netric\Account\Billing\AccountBillingServiceInterface;
use Netric\Log\LogInterface;
use Netric\WorkerMan\Worker\CronDailyWorker;

/**
 * Make sure that the scheudle runner will queue jobs
 *
 * @group integration
 */
class CronDailyWorkerTest extends TestCase
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

        // Create account container stub for returning the test account
        $this->mockAccountContainer = $this->createStub(AccountContainerInterface::class);
        $this->mockAccountContainer->method('getAllActiveAccounts')->willReturn([
            ['account_id' => $this->account->getAccountId()]
        ]);
        $this->mockAccountContainer->method('loadById')->willReturn($this->account);

        $log = $this->createStub(LogInterface::class);

        // Worker to test
        $this->worker = new CronDailyWorker(
            $this->mockAccountContainer,
            $this->createMock(AccountBillingServiceInterface::class),
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
