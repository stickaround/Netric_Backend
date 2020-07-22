<?php

namespace NetricTest\WorkerMan\Worker;

use Netric\WorkerMan\Job;
use PHPUnit\Framework\TestCase;
use Netric\WorkerMan\Worker\ScheduleRunnerWorker;
use Netric\WorkerMan\SchedulerService;
use Netric\WorkerMan\WorkerService;
use Netric\Entity\ObjType\EmailAccountEntity;
use Netric\Entity\ObjType\UserEntity;
use Netric\Account\Account;
use NetricTest\Bootstrap;
use InvalidArgumentException;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\ObjectTypes;
use Ramsey\Uuid\Uuid;

/**
 * Make sure that the scheudle runner will queue jobs
 */
class ScheduleRunnerWorkerTest extends TestCase
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

        // Worker to test
        $this->worker = new ScheduleRunnerWorker(
            $this->account->getApplication(),
            $this->schedulerService,
            $this->workerService
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
        $workerJob = $entityLoader->create(ObjectTypes::WORKER_JOB);
        $workerJob->setValue('entity_id', Uuid::uuid4()->toString());
        $workerJob->setValue("worker_name", "Test");
        $workerJob->setValue("job_data", json_encode(['myvar' => 'myval']));

        /*
         * Mock out service calls to simulate real-world interactions
         * with the test scheduled work
         */
        $this->schedulerService->method('getScheduledToRun')->willReturn([$workerJob]);
        $this->workerService->method('doWorkBackground')->willReturn($workerJob->getEntityid());

        $job = new Job();
        $job->setWorkload([
            "account_id" => $this->account->getAccountId(),
        ]);

        // Make sure it is a success
        $this->assertEquals([$workerJob->getEntityid()], $this->worker->work($job));
    }

    /**
     * Make sure that the job will not run if there is no account in the workload
     *
     * @return void
     */
    public function testWork_NoAccountThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);

        $job = new Job();
        // 'account_id' is a required element
        $job->setWorkload([]);

        // This should cause an exception to be thrown
        $this->worker->work($job);
    }
}
