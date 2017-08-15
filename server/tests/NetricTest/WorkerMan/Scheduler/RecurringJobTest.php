<?php
namespace NetricTest\WorkerMan\Scheduler;

use Netric\WorkerMan\Scheduler\RecurringJob;
use PHPUnit\Framework\TestCase;
use DateTime;

/**
 * Test the RecurringJob model
 */
class RecurringJobTest extends AbstractScheduledJobTests
{
    /**
     * This is used by the AbstractScheduledJobTests class to run each test
     *
     * @return ScheduledJob
     */
    protected function createNewJob()
    {
        return new RecurringJob();
    }

    /**
     * Verify that we can set and get the worker name
     */
    public function testSetAndGetWorkerName()
    {
        $recurringJob = new RecurringJob();
        $recurringJob->setWorkerName("Test");
        $this->assertEquals("Test", $recurringJob->getWorkerName());
    }

    /**
     * Make sure we can set and get job data (workload)
     */
    public function testSetAndGetJobData()
    {
        $jobData = ['account_id'=>1234, 'user_id'=>4321];
        $recurringJob = new RecurringJob();
        $recurringJob->setJobData($jobData);
        $this->assertEquals($jobData, $recurringJob->getJobData());
    }

    /**
     * Make sure we can get and set the recurrence unit
     */
    public function testSetAndGetIntervalUnit()
    {
        $recurringJob = new RecurringJob();
        $recurringJob->setIntervalUnit(RecurringJob::UNIT_MINUTE);
        $this->assertEquals(RecurringJob::UNIT_MINUTE, $recurringJob->getIntervalUnit());
    }

    /**
     * Verify the we can get and set the recurrence interval
     */
    public function testSetAndGetInterval()
    {
        $recurringJob = new RecurringJob();
        $recurringJob->setInterval(30);
        $this->assertEquals(30, $recurringJob->getInterval());
    }
}