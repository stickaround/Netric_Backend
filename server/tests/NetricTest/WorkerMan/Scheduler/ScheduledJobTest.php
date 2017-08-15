<?php
namespace NetricTest\WorkerMan;

use Netric\WorkerMan\Scheduler\ScheduledJob;
use PHPUnit\Framework\TestCase;
use DateTime;

/**
 * Test the ScheduledJob model
 */
class ScheduledJobTest extends TestCase
{
    /**
     * Verify that we can set and get the worker name
     */
    public function testSetAndGetWorkerName()
    {
        $scheduledJob = new ScheduledJob();
        $scheduledJob->setWorkerName("Test");
        $this->assertEquals("Test", $scheduledJob->getWorkerName());
    }

    /**
     * Verify that we can set and get the execute time
     */
    public function testSetAndGetExecuteTime()
    {
        $scheduledJob = new ScheduledJob();
        $now = new DateTime();
        $scheduledJob->setExecuteTime($now);
        $this->assertEquals($now, $scheduledJob->getExecuteTime());
    }

    /**
     * Make sure we can set and get job data (workload)
     */
    public function testSetAndGetJobData()
    {
        $jobData = ['account_id'=>1234, 'user_id'=>4321];
        $scheduledJob = new ScheduledJob();
        $scheduledJob->setJobData($jobData);
        $this->assertEquals($jobData, $scheduledJob->getJobData());
    }

    /**
     * Make sure that instances of recurring jobs and set and get the recurrence id
     */
    public function testSetAndGetRecurrenceId()
    {
        $scheduledJob = new ScheduledJob();
        $scheduledJob->setRecurrenceId(12);
        $this->assertEquals(12, $scheduledJob->getRecurrenceId());
    }
}