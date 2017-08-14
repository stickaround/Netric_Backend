<?php
namespace NetricTest\WorkerMan;

use Netric\WorkerMan\SchedulerService;
use Netric\WorkerMan\Scheduler\SchedulerDataMapperInterface;
use PHPUnit\Framework\TestCase;
use DateTime;

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
        $id = $this->scheduler->scheduleAtInterval('Test', $now, ['myvar'=>'testval']);

        $this->assertNotNull($id);
    }

    public function testGetScheduledToRun()
    {

    }

    public function testMarkCompleted()
    {

    }
}