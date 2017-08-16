<?php
namespace Netric\WorkerMan\Scheduler;

use DateTime;
use InvalidArgumentException;

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
     * Get a scheduled job by id
     *
     * @param int $scheduledJobId
     * @return ScheduledJob
     */
    public function getScheduledJob($scheduledJobId);


    /**
     * Delete a scheduled job from the database
     *
     * @param ScheduledJob $job The job to delete
     * @return bool true on success, false on failure
     * @throws InvalidArgumentException If the $job does not have an ID
     */
    public function deleteScheduledJob(ScheduledJob $job);

    /**
     * Get jobs that are scheduled to run up to a specific date
     *
     * This will return jobs in limited batches, so it needs to be called repeatedly
     * until it returns 0 jobs similar to how you would read a file with
     * fread() until it reaches EOF. We do this to make sure a huge backlog
     * of jobs cannot overload the scheduler.
     *
     * WARNING: It is important that as jobs get processed the setTimeExecuted
     * function gets called and the job is saved. If that does not happen then
     * calling while($this->>getQueuedScheduledJobs(...)) will result in
     * a dreaded endless loop.
     *
     * @param DateTime $toDate If null then the default will be 'now'
     * @return ScheduledJob[]
     */
    public function getQueuedScheduledJobs(DateTime $toDate = null);

    /**
     * Save a recurring job to the scheduler
     *
     * @param RecurringJob $recurringJob The job that will be executing at an interval
     * @return int $id of the recurrence
     */
    public function saveRecurringJob(RecurringJob $recurringJob);

    /**
     * Get a recurring job by id
     *
     * @param int $recurringJobId
     * @return RecurringJob
     */
    public function getRecurringJob($recurringJobId);


    /**
     * Get all recurring jobs
     *
     * @return RecurringJob[]
     */
    public function getAllRecurringJobs();
}
