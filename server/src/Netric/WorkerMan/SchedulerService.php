<?php
namespace Netric\WorkerMan;

use DateTime;
use Netric\WorkerMan\Scheduler\SchedulerDataMapperInterface;
use Netric\WorkerMan\Scheduler\ScheduledJob;
use Netric\WorkerMan\Scheduler\RecurringJob;

/**
 * Class SchedulerService will handle scheduling jobs to happen at a specific time or intervals
 */
class SchedulerService
{
    /**
     * Scheduler DataMapper
     *
     * @param SchedulerDataMapperInterface
     */
    private $dataMapper = null;

    /**
     * Setup the WorkerService
     *
     * @param SchedulerDataMapperInterface $dataMapper Used to get and save data
     */
    public function __construct(SchedulerDataMapperInterface $dataMapper)
    {
        $this->dataMapper = $dataMapper;
    }

    /**
     * Schedule a job to run at a specific date and time
     *
     * @param string $workerName The unique name of the worker to schedule
     * @param DateTime $execute Specific time to execute in the future
     * @param array $data Data to pass to the job when run
     * @return int Scheduled job ID
     */
    public function scheduleAtTime($workerName, DateTime $execute, array $data=[])
    {
    }

    /**
     * Schedule a job to run at a specific interval
     *
     * @param string $workerName
     * @param array $data Data to pass to the job when run
     * @param int $unit One of RecurringJob::UNIT_*
     * @param int $interval How many $units to wait between runs
     * @return int Recurring job id
     */
    public function scheduleAtInterval($workerName, array $data=[],  $unit, $interval)
    {
    }

    /**
     * Get scheduled jobs up to now or a specific data if passed
     *
     * @param DateTime|null $toDate If null then 'now' will be used to get jobs that should run now
     * @return ScheduledJob[]
     */
    public function getScheduledToRun(DateTime $toDate = null)
    {
        // TODO: Process recurring jobs to see if we need to make any new instances of scheduled jobs
        // NOTE: Be sure to set the $recurrenceId of the scheduled job so when it gets marked as
        //       complete we can update the last executed time of the recurrence

        // TODO: Get all scheduled jobs that should be executed now or before now
    }

    /**
     * Mark a job as complete which may flag or delete the task depending on the type
     *
     * @param int $scheduledId
     */
    public function markCompleted($scheduledId)
    {
        // TODO: If the job is part of a recurring series then make the last execute time of the recurrence
        // TODO: Delete the job from the scheduled queue (or put it into some sort of log)
    }
}
