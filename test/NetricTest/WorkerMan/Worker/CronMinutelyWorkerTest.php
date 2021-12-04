<?php

namespace NetricTest\WorkerMan\Worker;

use Netric\WorkerMan\Job;
use PHPUnit\Framework\TestCase;
use Netric\WorkerMan\Worker\ScheduleRunnerWorker;
use Netric\WorkerMan\SchedulerService;
use Netric\WorkerMan\WorkerService;
use Netric\Account\Account;
use NetricTest\Bootstrap;
use InvalidArgumentException;
use Netric\Account\AccountContainerInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Log\LogInterface;
use Netric\WorkerMan\Worker\CronMinutelyWorker;
use Ramsey\Uuid\Uuid;

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
     * Mock scheudler service to interact with
     *
     * @var SchedulerService
     */
    private $schedulerService = null;

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

        // Mock the scheduler service
        $this->schedulerService = $this->getMockBuilder(SchedulerService::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Create a mock worker service
        $this->workerService = $this->getMockBuilder(WorkerService::class)
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
            $this->schedulerService,
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
        // Create a temporary worker job to run
        $sl = $this->account->getServiceManager();
        $entityLoader = $sl->get(EntityLoaderFactory::class);
        $workerJob = $entityLoader->create(ObjectTypes::WORKER_JOB, $this->account->getAccountId());
        $workerJob->setValue('entity_id', Uuid::uuid4()->toString());
        $workerJob->setValue("worker_name", "Test");
        $workerJob->setValue("job_data", json_encode(['myvar' => 'myval']));

        /*
         * Mock out service calls to simulate real-world interactions
         * with the test scheduled work
         */
        $this->schedulerService->method('getScheduledToRun')->willReturn([$workerJob]);
        $jobPayload = json_decode($workerJob->getValue('job_data'), true);
        $jobPayload['account_id'] = $this->account->getAccountId();
        $this->workerService
            ->expects($this->once())
            ->method('doWorkBackground')
            ->with(
                $this->equalTo('Test'),
                $this->equalTo($jobPayload)
            )
            ->willReturn($workerJob->getEntityid());

        $job = new Job();

        // Make sure it is a success
        $this->assertTrue($this->worker->work($job));
    }
}
