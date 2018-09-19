<?php
namespace Netric\WorkerMan;

use DateTime;
use Netric\Entity\ObjType\WorkerJobEntity;
use Netric\EntityQuery;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\Entity\EntityLoader;
use Netric\WorkerMan\Scheduler\ScheduledJob;
use Netric\WorkerMan\Scheduler\RecurringJob;
use Netric\Entity\Recurrence\RecurrencePattern;
use Netric\EntityDefinition\ObjectTypes;

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
     * @param IndexInterface $entityIndex To query for worker job entities
     * @param EntityLoader $entityLoader Used to create and delete worker job entities
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
    public function scheduleAtTime($workerName, DateTime $execute, array $data = [])
    {
        $scheduledJob = $this->entityLoader->create(ObjectTypes::WORKER_JOB);
        $scheduledJob->setValue('worker_name', $workerName);
        $scheduledJob->setValue('ts_scheduled', $execute->getTimestamp());
        $scheduledJob->setValue('job_data', json_encode($data));
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
    public function scheduleAtInterval($workerName, array $data = [], $type, $interval)
    {
        $scheduledJob = $this->entityLoader->create(ObjectTypes::WORKER_JOB);
        $scheduledJob->setValue('worker_name', $workerName);
        $scheduledJob->setValue('job_data', json_encode($data));
        $scheduledJob->setValue("ts_scheduled", time());

        // Create a new recurrence pattern from unit and interval
        $recurrence = new RecurrencePattern();
        $recurrence->setInterval($interval);
        $recurrence->setRecurType($type);
        $recurrence->setDateStart(new DateTime());
        $scheduledJob->setRecurrencePattern($recurrence);

        return $this->entityLoader->save($scheduledJob);
    }

    /**
     * Get scheduled jobs up to now or a specific data if passed
     *
     * @param DateTime|null $toDate If null then 'now' will be used to get jobs
     *                      that should run now
     * @param string $workerName If set just look for a specific worker
     * @return WorkerJobEntity[]
     */
    public function getScheduledToRun(DateTime $toDate = null, $workerName = "")
    {
        $jobsToReturn = [];

        // We will default to now if no date was passed
        if ($toDate === null) {
            $toDate = new DateTime();
        }

        $query = new EntityQuery(ObjectTypes::WORKER_JOB);
        $query->where('ts_scheduled')->isLessOrEqualTo($toDate->getTimestamp());
        
        // If we are looking for a specific worker name then add it to the filter
        if ($workerName) {
            $query->andWhere('worker_name')->equals($workerName);
        }

        $query->setLimit(1000);
        $result = $this->entityIndex->executeQuery($query);
        for ($i = 0; $i < $result->getNum(); $i++) {
            $jobsToReturn[] = $result->getEntity($i);
        }

        return $jobsToReturn;
    }

    /**
     * When a job has started we remove it from the queue
     *
     * In the case where a scheduled job is part of a recurring series, then
     * this function will also update the last executed timestamp of the recurring job.
     *
     * @param WorkerJobEntity $scheduledJob
     */
    public function setJobAsExecuted(WorkerJobEntity $scheduledJob)
    {
        if (!$scheduledJob->getId()) {
            throw new \RuntimeException("Cannot mark an unsaved job as complete");
        }

        // Set the scheduled job as executed which should remove it from any queues for nex time
        $scheduledJob->setValue("ts_executed", time());
        $this->entityLoader->save($scheduledJob);
    }

    /**
     * Add scheduled jobs to the queue and return immediately with a job handle (id)
     *
     * Work can be deferred until a later date, this will get work that should execute on
     * or before the provided date and submit the work as jobs.
     *
     * @param \DateTime $timeRunBy Get jobs that should have run on or before
     *        this date. If the value is null then now in UTC will be used.
     * @return array("A unique id/handle to the queued job")
     */
    public function doScheduledWork(\DateTime $timeRunBy = null)
    {
        $jobIds = [];
        $scheduledWork = $this->scheduler->getScheduledToRun();
        foreach ($scheduledWork as $scheduled) {
            $jobIds[] = $this->doWorkBackground(
                $scheduled['worker_name'],
                $scheduled['job_data']
            );

            // Make sure we don't try to execute this job again
            $this->scheduler->setJobAsExecuted($scheduled['id']);
        }
        return $jobIds;
    }
}
