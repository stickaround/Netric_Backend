<?php
namespace NetricTest\WorkerMan\Scheduler;

use Netric\WorkerMan\Scheduler\SchedulerDataMapperInterface;
use PHPUnit\Framework\TestCase;

/**
 * Common tests for all scheduler DataMapper
 */
abstract class AbstractSchedulerDataMapperTests extends TestCase
{
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

    }
}