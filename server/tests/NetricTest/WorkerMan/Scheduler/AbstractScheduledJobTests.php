<?php
namespace NetricTest\WorkerMan\Scheduler;

use Netric\WorkerMan\Scheduler\AbstractScheduledJob;
use PHPUnit\Framework\TestCase;
use DateTime;

/**
 * Common tests for any class that extends AbstractScheduledJob
 */
abstract class AbstractScheduledJobTests extends TestCase
{
    /**
     * This is required by any extended classes to get an instance of a scheduled job
     *
     * @return AbstractScheduledJob
     */
    abstract protected function createNewJob();

    /**
     * Make sure we can set the ID for both scheduled or recurring jobs
     */
    public function testSetAndGetId()
    {
        $scheduledJob = $this->createNewJob();
        $scheduledJob->setId(1234);
        $this->assertEquals(1234, $scheduledJob->getId());
    }

    /**
     * Verify that we can set and get the worker name
     */
    public function testSetAndGetWorkerName()
    {
        $scheduledJob = $this->createNewJob();
        $scheduledJob->setWorkerName("Test");
        $this->assertEquals("Test", $scheduledJob->getWorkerName());
    }

    /**
     * Make sure we can set and get job data (workload)
     */
    public function testSetAndGetJobData()
    {
        $jobData = ['account_id'=>1234, 'user_id'=>4321];
        $scheduledJob = $this->createNewJob();
        $scheduledJob->setJobData($jobData);
        $this->assertEquals($jobData, $scheduledJob->getJobData());
    }

    /**
     * Make sure we can get and set the last time the job or job instance was executed
     */
    public function testSetAndGetTimeExecuted()
    {
        $scheduledJob = $this->createNewJob();
        $now = new DateTime();
        $scheduledJob->setTimeExecuted($now);
        $this->assertEquals($now, $scheduledJob->getTimeExecuted());
    }
}