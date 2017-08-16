<?php
namespace NetricTest\WorkerMan\Scheduler;

use Netric\WorkerMan\Scheduler\RecurringJob;
use DateTime;
use DateInterval;

/**
 * Test the RecurringJob model
 *
 * We extend AbstractScheduledJobTests to inherit
 * some default tests.
 */
class RecurringJobTest extends AbstractScheduledJobTests
{
    /**
     * This is used by the AbstractScheduledJobTests class to run each test
     *
     * @return RecurringJob
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

    /**
     * Test to make sure next execute time works with a minute unit
     */
    public function testGetNextExecuteTime_Minute()
    {
        $now = new DateTime();

        $recurringJob = new RecurringJob();
        $recurringJob->setIntervalUnit(RecurringJob::UNIT_MINUTE);
        $recurringJob->setInterval(2);
        $recurringJob->setTimeExecuted($now);

        // Next time execute should be two minutes from now
        $nextExecute = $recurringJob->getNextExecuteTime();
        $this->assertEquals($now->add(new DateInterval("PT1M")), $nextExecute);
    }

    /**
     * Test to make sure next execute time works with a hour unit
     */
    public function testGetNextExecuteTime_Hour()
    {
        $now = new DateTime();

        $recurringJob = new RecurringJob();
        $recurringJob->setIntervalUnit(RecurringJob::UNIT_HOUR);
        $recurringJob->setInterval(1);
        $recurringJob->setTimeExecuted($now);

        // Next time execute should be one hour from now
        $nextExecute = $recurringJob->getNextExecuteTime();
        $this->assertEquals($now->add(new DateInterval("PT1H")), $nextExecute);
    }

    /**
     * Test to make sure next execute time works with a day unit
     */
    public function testGetNextExecuteTime_Day()
    {
        $now = new DateTime();

        $recurringJob = new RecurringJob();
        $recurringJob->setIntervalUnit(RecurringJob::UNIT_DAY);
        $recurringJob->setInterval(4);
        $recurringJob->setTimeExecuted($now);

        // Next time execute should be four days from now
        $nextExecute = $recurringJob->getNextExecuteTime();
        $this->assertEquals($now->add(new DateInterval("P4D")), $nextExecute);
    }

    /**
     * Test to make sure next execute time works with a month unit
     */
    public function testGetNextExecuteTime_Month()
    {
        $now = new DateTime();

        $recurringJob = new RecurringJob();
        $recurringJob->setIntervalUnit(RecurringJob::UNIT_MONTH);
        $recurringJob->setInterval(2);
        $recurringJob->setTimeExecuted($now);

        // Next time execute should be two months from now
        $nextExecute = $recurringJob->getNextExecuteTime();
        $this->assertEquals($now->add(new DateInterval("P2M")), $nextExecute);
    }
}