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
        $scheduledJob = new ScheduledJob();
        $scheduledJob->setWorkerName($workerName);
        $scheduledJob->setExecuteTime($execute);
        $scheduledJob->setJobData($data);
        return $this->dataMapper->saveScheduledJob($scheduledJob);
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
        $recurringJob = new RecurringJob();
        $recurringJob->setWorkerName($workerName);
        $recurringJob->setJobData($data);
        $recurringJob->setInterval($interval);
        $recurringJob->setIntervalUnit($unit);
        return $this->dataMapper->saveRecurringJob($recurringJob);
    }

    /**
     * Get scheduled jobs up to now or a specific data if passed
     *
     * @param DateTime|null $toDate If null then 'now' will be used to get jobs
     *                      that should run now
     * @return ScheduledJob[]
     */
    public function getScheduledToRun(DateTime $toDate = null)
    {
        // We will default to now if no date was passed
        if ($toDate === null) {
            $toDate = new DateTime();
        }

        // Process recurring jobs to see if we need to make any instances of scheduled jobs
        $this->createScheduledFromRecurringJobs($toDate);

        // Return all queued jobs - including instances of recurring jobs created above
        return $this->dataMapper->getQueuedScheduledJobs($toDate);
    }

    /**
     * When a job has started we remove it from the queue
     *
     * In the case where a scheduled job is part of a recurring series, then
     * this function will also update the last executed timestamp of the recurring job.
     *
     * @param ScheduledJob $scheduledJob
     */
    public function setJobAsExecuted(ScheduledJob $scheduledJob)
    {
        if (!$scheduledJob->getId()) {
            throw new \RuntimeException("Cannot mark an unsaved job as complete");
        }

        /*
         * If the job is part of a recurring series then make the
         * last execute time of the recurrence
         */
        if ($scheduledJob->getRecurrenceId()) {
            $recurId = $scheduledJob->getRecurrenceId();
            $recurringJob = $this->dataMapper->getRecurringJob($recurId);
            $recurringJob->setTimeExecuted(new DateTime());
            $this->dataMapper->saveRecurringJob($recurringJob);
        }

        // Set the scheduled job as executed which should remove it from any queues for nex time
        $scheduledJob->setTimeExecuted(new DateTime());
        $this->dataMapper->saveScheduledJob($scheduledJob);
    }

    /**
     * Process recurring jobs and schedule them to run if appropriate
     *
     * This will loop through any recurring jobs and if they should be run on
     * or before the $toDate supplied param, then add a scheduled job to the queue
     * to be executed.
     *
     * @param DateTime $toDate
     */
    private function createScheduledFromRecurringJobs(DateTime $toDate)
    {
        // Get jobs that have not been executed after $toDate
        //$recurringJobs = $this->dataMapper->getRecurringJobsNotExecutedAfter($toDate);
        // Get jobs who have not been executed after
        // getRecurringJobs
    }
}
