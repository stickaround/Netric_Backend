<?php
namespace NetricTest\WorkerMan;

use Netric\WorkerMan\Scheduler\AbstractScheduledJob;
use PHPUnit\Framework\TestCase;
use DateTime;

/**
 * Common tests for any class that extends AbstractScheduledJob
 */
abstract class AbstractScheduledJobTests extends TestCase
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
     * Make sure we can set and get job data (workload)
     */
    public function testSetAndGetJobData()
    {
        $jobData = ['account_id'=>1234, 'user_id'=>4321];
        $scheduledJob = new ScheduledJob();
        $scheduledJob->setJobData($jobData);
        $this->assertEquals($jobData, $scheduledJob->getJobData());
    }
}