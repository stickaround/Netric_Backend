<?php
namespace Netric\WorkerMan;

use DateTime;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\EntityLoader;
use Netric\WorkerMan\Scheduler\ScheduledJob;
use Netric\WorkerMan\Scheduler\RecurringJob;
use Netric\Entity\Recurrence\RecurrencePattern;

/**
 * Class SchedulerService will handle scheduling jobs to happen at a specific time or intervals
 */
class SchedulerService
{
    /**
     * Entity index to query worker_job(s)
     *
     * @param IndexInterface
     */
    private $entityIndex = null;

    /**
     * Loader to load and save entities
     *
     * @var EntityLoader|null
     */
    private $entityLoader = null;

    /**
     * Setup the WorkerService
     *
     * @param IndexInterface $entityIndex To query for worker jobs
     */
    public function __construct(IndexInterface $entityIndex, EntityLoader $entityLoader)
    {
        $this->entityIndex = $entityIndex;
        $this->entityLoader = $entityLoader;
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
        $scheduledJob = $this->entityLoader->create('worker_job');
        $scheduledJob->setValue('worker_name', $workerName);
        $scheduledJob->setValue('ts_execute', $execute->getTimestamp());
        $scheduledJob->seValue('job_data', json_encode($data));
        return $this->entityLoader->save($scheduledJob);
    }

    /**
     * Schedule a job to run at a specific interval
     *
     * @param string $workerName
     * @param array $data Data to pass to the job when run
     * @param int $type One of RecurrencePattern::RECUR_*
     * @param int $interval How many $units to wait between runs
     * @return int Recurring job id
     */
    public function scheduleAtInterval($workerName, array $data=[], $type, $interval)
    {
        $scheduledJob = $this->entityLoader->create('worker_job');
        $scheduledJob->setValue('worker_name', $workerName);
        $scheduledJob->seValue('job_data', json_encode($data));

        // Create a new recurrence pattern from unit and interval
        $recurrence = new RecurrencePattern();
        $recurrence->setInterval($interval);
        $recurrence->setRecurType($type);

        return $this->entityLoader->save($scheduledJob);
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
        $recurringJobs = $this->dataMapper->getAllRecurringJobs();

        // Check each recurrence pattern to see if it is time to schedule a new job
        foreach ($recurringJobs as $recurringJob) {
            /*
             * We only need to check if there are no executed jobs later than $toDate
             * or no jobs have ever been executed.
             */
            if ($recurringJob->getTimeExecuted() === null ||
                $recurringJob->getTimeExecuted() < $toDate) {
                // Check to recurrence pattern to see if we should execute
                $nextExecuteTime = $recurringJob->getNextExecuteTime();
                if ($nextExecuteTime <= $toDate) {
                    // Create a new scheduled job from the pattern
                    $scheduledJob = new ScheduledJob();
                    $scheduledJob->setRecurrenceId($recurringJob->getId());
                    $scheduledJob->setWorkerName($recurringJob->getWorkerName());
                    $scheduledJob->setJobData($recurringJob->getJobData());
                    $scheduledJob->setExecuteTime($nextExecuteTime);
                    $this->dataMapper->saveScheduledJob($scheduledJob);

                    // Indicate that we have just executed by creating a scheduled job
                    $recurringJob->setTimeExecuted(new DateTime());
                    $this->dataMapper->saveRecurringJob($recurringJob);
                }
            }
        }
    }


}
