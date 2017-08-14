<?php
namespace Netric\WorkerMan\Scheduler;

use DateTime;

interface SchedulerDataMapperInterface
{
    /**
     * Save a scheduled job
     *
     * @param ScheduledJob $job
     * @return int $id of saved job on success
     */
    public function saveScheduledJob(ScheduledJob $job);

    /**
     * Save a recurring job to the scheduler
     *
     * @param RecurringJob $recurringJob The job that will be executing at an interval
     * @return int $id of the recurrence
     */
    public function saveRecurringJob(RecurringJob $recurringJob);
}