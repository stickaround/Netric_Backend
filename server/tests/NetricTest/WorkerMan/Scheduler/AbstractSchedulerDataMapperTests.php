<?php
namespace NetricTest\WorkerMan\Scheduler;

use Netric\WorkerMan\Scheduler\ScheduledJob;
use Netric\WorkerMan\Scheduler\SchedulerDataMapperInterface;
use PHPUnit\Framework\TestCase;
use DateTime;
use DateInterval;
use Zend\Validator\Date;

/**
 * Common tests for all scheduler DataMapper
 */
abstract class AbstractSchedulerDataMapperTests extends TestCase
{
    /**
     * Buffer for scheduled jobs we need to cleanup on tearDown
     *
     * @var ScheduledJob[]
     */
    private $scheduledJobsToCleanUp = [];

    /**
     * Clean-up
     */
    protected function tearDown()
    {
        $dataMapper = $this->getDataMapper();

        foreach ($this->scheduledJobsToCleanUp as $scheduledJob) {
            if ($scheduledJob->getId()) {
                $dataMapper->deleteScheduledJob($scheduledJob);
            }
        }
    }

    /**
     * This is required by any extended classes to get the concrete dataMapper instance
     *
     * @return SchedulerDataMapperInterface
     */
    abstract protected function getDataMapper();

    /**
     * Verify that we can save a scheduled task
     */
    public function testSaveScheduledJob()
    {
        $dataMapper = $this->getDataMapper();

        // Create a test job
        $scheduledJob = new ScheduledJob();
        $scheduledJob->setWorkerName("Test");
        $scheduledJob->setExecuteTime(new DateTime());
        $scheduledJob->setJobData(['myvar'=>'test']);
        $scheduledJob->setRecurrenceId(123);

        // Save the new scheduled job
        $jobId = $dataMapper->saveScheduledJob($scheduledJob);

        // Queue for cleanup
        $this->scheduledJobsToCleanUp[] = $scheduledJob;

        // Make sure we got a valid ID
        $this->assertGreaterThan(0, $jobId);
    }

    /**
     * Make sure we can retrieve a scheduled job and all the properties
     */
    public function testGetScheduledJob()
    {
        $dataMapper = $this->getDataMapper();

        $executeTomorrow = new DateTime(date("Y-m-d H:i:s"));
        $executeTomorrow->add(new DateInterval("P1D"));
        $data = [
            'worker_name' => "Test",
            'execute_time' => $executeTomorrow,
            'executed_time' => new DateTime(date("Y-m-d H:i:s")),
            'job_data' => ['myvar'=>'test'],
            'recurrence_id' => 123,
        ];

        // Create a test job
        $scheduledJob = new ScheduledJob();
        $scheduledJob->setWorkerName($data['worker_name']);
        $scheduledJob->setExecuteTime($data['execute_time']);
        $scheduledJob->setTimeExecuted($data['executed_time']);
        $scheduledJob->setJobData($data['job_data']);
        $scheduledJob->setRecurrenceId($data['recurrence_id']);

        // Save the new scheduled job
        $jobId = $dataMapper->saveScheduledJob($scheduledJob);

        // Queue for cleanup
        $this->scheduledJobsToCleanUp[] = $scheduledJob;

        // Get the job and create a new array to compare against
        $loadedJob = $dataMapper->getScheduledJob($jobId);
        $loadedData = [
            'worker_name' => $loadedJob->getWorkerName(),
            'execute_time' => $loadedJob->getExecuteTime(),
            'executed_time' => $loadedJob->getTimeExecuted(),
            'job_data' => $loadedJob->getJobData(),
            'recurrence_id' => $loadedJob->getRecurrenceId(),
        ];

        // Make sure the data we saved and the data we loaded are the same
        $this->assertEquals($data, $loadedData);
    }

    /**
     * Verify that deleting a job means you cannot open it again
     */
    public function testDeleteScheduledJob()
    {
        $dataMapper = $this->getDataMapper();

        // Create a test job
        $scheduledJob = new ScheduledJob();
        $scheduledJob->setWorkerName("Test");
        $scheduledJob->setExecuteTime(new DateTime());
        $scheduledJob->setJobData(['myvar'=>'test']);
        $scheduledJob->setRecurrenceId(123);

        // Save the new scheduled job
        $jobId = $dataMapper->saveScheduledJob($scheduledJob);

        // Now delete it
        $dataMapper->deleteScheduledJob($scheduledJob);

        // Make sure we cannot get the deleted job
        $this->assertNull($dataMapper->getScheduledJob($jobId));
    }

    /**
     * Validate that we can queue a job to run now and it will be returned
     */
    public function testGetQueuedScheduledJobs()
    {
        $dataMapper = $this->getDataMapper();

        // Create a test job
        $scheduledJob = new ScheduledJob();
        $scheduledJob->setWorkerName("Test");
        $scheduledJob->setExecuteTime(new DateTime());

        // Save the new scheduled job
        $jobId = $dataMapper->saveScheduledJob($scheduledJob);

        // Queue for cleanup
        $this->scheduledJobsToCleanUp[] = $scheduledJob;

        // Get all scheduled jobs
        $jobsToRun = $dataMapper->getQueuedScheduledJobs();

        // Check to see if the job we just created is in the array
        $inArray = false;
        foreach ($jobsToRun as $queuedJob) {
            if ($queuedJob->getId() === $jobId) {
                $inArray = true;
                break;
            }
        }

        $this->assertTrue($inArray);
    }

    /**
     * Make sure that jobs that have already been executed are not returned again
     */
    public function testGetQueuedScheduledJobs_ExcludeExecuted()
    {
        $dataMapper = $this->getDataMapper();

        // Create a test job
        $scheduledJob = new ScheduledJob();
        $scheduledJob->setWorkerName("Test");
        $scheduledJob->setExecuteTime(new DateTime());
        $scheduledJob->setTimeExecuted(new DateTime());

        // Save the new scheduled job
        $jobId = $dataMapper->saveScheduledJob($scheduledJob);

        // Queue for cleanup
        $this->scheduledJobsToCleanUp[] = $scheduledJob;

        // Get all scheduled jobs
        $jobsToRun = $dataMapper->getQueuedScheduledJobs();

        // Check to see if the job we just created is in the array
        $inArray = false;
        foreach ($jobsToRun as $queuedJob) {
            if ($queuedJob->getId() === $jobId) {
                $inArray = true;
                break;
            }
        }

        $this->assertFalse($inArray);
    }

    /**
     * Make sure that jobs set to run the future are not prematurely executed
     */
    public function testGetQueuedScheduledJobs_ExcludeFuture()
    {
        $dataMapper = $this->getDataMapper();

        // Create a test job
        $scheduledJob = new ScheduledJob();
        $scheduledJob->setWorkerName("Test");

        // Set this to run tomorrow
        $tomorrow = new DateTime();
        $tomorrow->add(new DateInterval("P1D"));
        $scheduledJob->setExecuteTime($tomorrow);

        // Save the new scheduled job
        $jobId = $dataMapper->saveScheduledJob($scheduledJob);

        // Queue for cleanup
        $this->scheduledJobsToCleanUp[] = $scheduledJob;

        // Get all scheduled jobs
        $jobsToRun = $dataMapper->getQueuedScheduledJobs();

        // Check to see if the job we just created is in the array
        $inArray = false;
        foreach ($jobsToRun as $queuedJob) {
            if ($queuedJob->getId() === $jobId) {
                $inArray = true;
                break;
            }
        }

        $this->assertFalse($inArray);
    }
}