<?php

namespace NetricTest\WorkerMan;

use NetricTest\Bootstrap;
use Netric\WorkerMan\SchedulerService;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\Entity\EntityLoader;
use Netric\Entity\Recurrence\RecurrencePattern;
use PHPUnit\Framework\TestCase;
use DateTime;
use DateInterval;
use Zend\Validator\Date;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\ObjectTypes;

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
    protected function setUp(): void
    {
        // Get globally setup account
        $serviceLocator = Bootstrap::getAccount()->getServiceManager();

        $this->entityIndex = $serviceLocator->get(IndexFactory::class);
        $this->entityLoader = $serviceLocator->get(EntityLoaderFactory::class);

        $this->scheduler = new SchedulerService($this->entityIndex, $this->entityLoader);
    }

    /**
     * Cleanup
     */
    protected function tearDown(): void
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
        $id = $this->scheduler->scheduleAtTime('Test', $now, ['myvar' => 'testval']);
        $this->tempEntitiesToDelete[] = $this->entityLoader->getByGuid($id);

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
            ['myvar' => 'testval'],
            RecurrencePattern::RECUR_DAILY,
            1
        );
        $this->tempEntitiesToDelete[] = $this->entityLoader->getByGuid($id);

        $this->assertNotNull($id);
    }

    /**
     * Test getting all scheduled jobs
     */
    public function testGetScheduledToRun()
    {
        // Create a scheduled job to run now
        $now = new DateTime();
        $id = $this->scheduler->scheduleAtTime('Test', $now, ['myvar' => 'testval']);
        $this->tempEntitiesToDelete[] = $this->entityLoader->getByGuid($id);

        $jobs = $this->scheduler->getScheduledToRun();

        $jobFound = false;
        foreach ($jobs as $job) {
            if ($job->getEntityId() == $id) {
                $jobFound = true;
                break;
            }
        }
        $this->assertTrue($jobFound);
    }

    /**
     * Test getting scheduled recurring jobs
     */
    public function testGetScheduledToRunRecurring_FirstRun()
    {
        // Create a job that should recur every day
        $id = $this->scheduler->scheduleAtInterval(
            'Test',
            ['myvar' => 'testval'],
            RecurrencePattern::RECUR_DAILY,
            1
        );
        $this->tempEntitiesToDelete[] = $this->entityLoader->getByGuid($id);

        // Get scheduled jobs for the next three days
        $runTo = new DateTime();
        $runTo->add(new DateInterval("P3D"));
        $jobs = $this->scheduler->getScheduledToRun($runTo);

        // Queue for cleanup
        foreach ($jobs as $job) {
            $this->tempEntitiesToDelete[] = $job;
        }

        // Assert that we found at least three jobs
        $this->assertGreaterThanOrEqual(3, count($jobs));
    }

    /**
     * Make sure we can set a job (and associated recurrence) as executed
     */
    public function testSetJobAsExecuted()
    {
        $now = new DateTime();
        $id = $this->scheduler->scheduleAtTime('Test', $now, ['myvar' => 'testval']);
        $jobEntity = $this->entityLoader->getByGuid($id);
        $this->tempEntitiesToDelete[] = $jobEntity;

        // Set a scheduled job as completed
        $this->scheduler->setJobAsExecuted($jobEntity);

        // Make sure the the execute time of the scheduled job was set
        $this->assertNotNull($jobEntity->getValue("ts_executed"));
    }
}
