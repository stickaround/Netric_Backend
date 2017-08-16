<?php
namespace NetricTest\WorkerMan;

use Netric\WorkerMan\Scheduler\RecurringJob;
use Netric\WorkerMan\SchedulerService;
use Netric\WorkerMan\Scheduler\ScheduledJob;
use Netric\WorkerMan\Scheduler\SchedulerDataMapperInterface;
use PHPUnit\Framework\TestCase;
use DateTime;
use DateInterval;

/**
 * Class SchedulerServiceTest
 *
 * Validate that we can schedule workers
 *
 * @package NetricTest\WorkerMan
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
     * Set a mock data mapper so we can interact with the service without needing a DB
     *
     * @var SchedulerDataMapperInterface
     */
    private $mockDataMapper = null;

    /**
     * Setup the service
     */
    protected function setUp()
    {
        // Setup a mock definition loader since we don't want to test all definitions
        $this->mockDataMapper = $this->getMockBuilder(SchedulerDataMapperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scheduler = new SchedulerService($this->mockDataMapper);
    }

    /**
     * Test adding a new scheduled job to the queue
     */
    public function testScheduleAtTime()
    {
        // The datamapper function should return the saved ID
        $this->mockDataMapper->method('saveScheduledJob')->willReturn(12345);

        // Add the job to the queue
        $now = new DateTime();
        $id = $this->scheduler->scheduleAtTime('Test', $now, ['myvar'=>'testval']);

        $this->assertNotNull($id);
    }

    /**
     * Test adding a job that is recurring
     */
    public function testScheduleAtInterval()
    {
        // The datamapper function should return the saved ID
        $this->mockDataMapper->method('saveRecurringJob')->willReturn(12345);

        // Add the job to the queue
        $now = new DateTime();
        $id = $this->scheduler->scheduleAtInterval(
            'Test',
            ['myvar'=>'testval'],
            RecurringJob::UNIT_HOUR,
            1
        );

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

        // Right now the datamapper will only return the one job
        $this->mockDataMapper->method('getQueuedScheduledJobs')->willReturn([$scheduledJob]);

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
        // Create a recurring job that the scheduled job will be an instance of
        $recurringJob = new RecurringJob();
        $recurringJob->setId(111);

        // Create a scheduled job to return from the mock datamapper
        $scheduledJob = new ScheduledJob();
        $scheduledJob->setId(222);
        $scheduledJob->setWorkerName("Test");
        $scheduledJob->setExecuteTime((new DateTime()));
        $scheduledJob->setJobData([]);

        /*
         * Make the scheduled job an instance of our recurring job so we
         * can test that marking the scheduled job sets the last execute time of the
         * recurring job
         */
        $scheduledJob->setRecurrenceId($recurringJob->getId());

        // Make the mock datamapper return and save our recurring and scheduled jobs
        $this->mockDataMapper->method('getScheduledJob')->willReturn($scheduledJob);
        $this->mockDataMapper->method('saveScheduledJob')->willReturn($scheduledJob->getId());

        // Set a scheduled job as completed
        $this->scheduler->setJobAsExecuted($scheduledJob);

        // Make sure the the execute time of the scheduled job was set
        $this->assertNotNull($scheduledJob->getTimeExecuted());
    }
}
