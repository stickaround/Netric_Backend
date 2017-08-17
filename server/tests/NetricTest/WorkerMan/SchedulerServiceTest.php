<?php
namespace NetricTest\WorkerMan;

use NetricTest\Bootstrap;
use Netric\WorkerMan\Scheduler\RecurringJob;
use Netric\WorkerMan\SchedulerService;
use Netric\WorkerMan\Scheduler\ScheduledJob;
use Netric\WorkerMan\Scheduler\SchedulerDataMapperInterface;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\EntityLoader;
use PHPUnit\Framework\TestCase;
use DateTime;
use DateInterval;

/**
 * Class SchedulerServiceTest
 *
 * Validate that we can schedule workers
 *
 * @group integration
 */
class SchedulerServiceTest extends TestCase
{
    /**
     * Scheduler service to test
     *
     * @var SchedulerService
     */
    private $scheduler = null;

    /**
     * Mock entity index
     *
     * @var IndexInterface
     */
    private $entityIndex = null;

    /**
     * Mock entity loader to get and save entities
     *
     * @var EntityLoader
     */
    private $entityLoader = null;


    private $tempEntitiesToDelete = [];

    /**
     * Setup the service
     */
    protected function setUp()
    {
        // Get globally setup account
        $serviceLocator = Bootstrap::getAccount();

        $this->entityIndex = $serviceLocator->get("Netric/EntityQuery/Index/Index");
        $this->entityLoader = $serviceLocator->get("EntityLoader");

        $this->scheduler = new SchedulerService($this->entityIndex, $this->entityloader);
    }

    /**
     * Cleanup
     */
    protected function tearDown()
    {
        foreach ($this->tempEntitiesToDelete as $entity) {
            $this->entityLoader->delete($entity, true);
        }
    }

    /**
     * Test adding a new scheduled job to the queue
     */
    public function testScheduleAtTime()
    {
        // Add the job to the queue
        $now = new DateTime();
        $id = $this->scheduler->scheduleAtTime('Test', $now, ['myvar'=>'testval']);
        $this->tempEntitiesToDelete[] = $this->entityLoader->get('worker_job', $id);

        $this->assertNotNull($id);
    }

    /**
     * Test adding a job that is recurring
     */
    public function testScheduleAtInterval()
    {
        // Add the job to the queue
        $now = new DateTime();
        $id = $this->scheduler->scheduleAtInterval(
            'Test',
            ['myvar'=>'testval'],
            RecurringJob::UNIT_HOUR,
            1
        );
        $this->tempEntitiesToDelete[] = $this->entityLoader->get('worker_job', $id);

        $this->assertNotNull($id);
    }

    /**
     * Test getting all scheduled jobs
     */
    public function testGetScheduledToRun()
    {
        // Create a scheduled job to return from the mock datamapper
        $scheduledJob = new ScheduledJob();
        $scheduledJob->setId(111);
        $scheduledJob->setWorkerName("Test");
        $scheduledJob->setExecuteTime((new DateTime()));
        $scheduledJob->setJobData([]);

        // Make sure getScheduledRun called the datamapper correctly
        $this->assertEquals([$scheduledJob], $this->scheduler->getScheduledToRun());
    }

    /**
     * Test getting scheduled recurring jobs
     */
    public function testGetScheduledToRunRecurring_FirstRun()
    {
        // Create a scheduled job to return from the mock datamapper
        $recurringJob = new RecurringJob();
        $recurringJob->setId(111);
        $recurringJob->setWorkerName("Test");
        $recurringJob->setJobData([]);

        // Run job every 1 day
        $recurringJob->setIntervalUnit(RecurringJob::UNIT_DAY);
        $recurringJob->setInterval(1);

        // Make sure that getAllRecurringJobs returns only the test Job above
        $this->mockDataMapper->method('getAllRecurringJobs')->willReturn([$recurringJob]);

        /*
         * The service should analyze the recurring job and create a new
         * ScheduledJob based on the RecurringJob.
         */
        $this->mockDataMapper->expects($this->once())
            ->method('saveScheduledJob')
            ->with($this->isInstanceOf(ScheduledJob::class));

        // Run with recurring jobs
        $this->scheduler->getScheduledToRun();
    }

    /**
     * Test getting scheduled recurring jobs sets last executed time of recurrence
     */
    public function testGetScheduledToRunRecurringSetsLastExecuted()
    {
        // Create a scheduled job to return from the mock datamapper
        $recurringJob = new RecurringJob();
        $recurringJob->setId(2);
        $recurringJob->setWorkerName("Test");
        $recurringJob->setJobData([]);

        // Run job every 1 day
        $recurringJob->setIntervalUnit(RecurringJob::UNIT_DAY);
        $recurringJob->setInterval(1);

        // Make sure that getAllRecurringJobs returns only the test Job above
        $this->mockDataMapper->method('getAllRecurringJobs')->willReturn([$recurringJob]);

        // Run with recurring jobs which should set last executed in the recurrence
        $this->scheduler->getScheduledToRun();

        $this->assertNotNull($recurringJob->getTimeExecuted());
    }

    /**
     * Make sure that recurring jobs will not create duplicates
     *
     * Create a recurring job that was last executed through tomorrow so no
     * new scheduled jobs should be created. In the 'real world' this would
     * effectively mean that the recurrence was already processed today
     * or should not be run today due to the recurrence pattern
     */
    public function testGetScheduledToRunRecurring_NoOverlap()
    {
        $tomorrow = new DateTime();
        $tomorrow->add(new DateInterval("P1D"));

        $recurringJob = new RecurringJob();
        $recurringJob->setId(111);
        $recurringJob->setTimeExecuted($tomorrow);
        $recurringJob->setWorkerName("Test");
        $recurringJob->setJobData([]);

        // Run job every 1 day
        $recurringJob->setIntervalUnit(RecurringJob::UNIT_DAY);
        $recurringJob->setInterval(1);

        // Make sure that getAllRecurringJobs returns only the test Job above
        $this->mockDataMapper->method('getAllRecurringJobs')->willReturn([$recurringJob]);

        // A ScheduledJob should not be created since the recurrence was
        // already executed through tomorrow
        $this->mockDataMapper->expects($this->never())
            ->method('saveScheduledJob')
            ->with($this->anything());

        // Run with recurring jobs
        $this->scheduler->getScheduledToRun();
    }

    /**
     * Make sure we can set a job (and associated recurrence) as executed
     */
    public function testSetJobAsExecuted()
    {

        // Set a scheduled job as completed
        $this->scheduler->setJobAsExecuted($scheduledJob);

        // Make sure the the execute time of the scheduled job was set
        $this->assertNotNull($scheduledJob->getTimeExecuted());
    }
}
