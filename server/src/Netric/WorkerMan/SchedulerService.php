<?php
namespace Netric\WorkerMan;

use DateTime;
use Netric\Application\DataMapperInterface;

/**
 * Class SchedulerService will handle scheduling things to happen at a specific time or intervals
 */
class SchedulerService
{
    /**
     * Define units for intervals
     */
    const UNIT_SECOND = 1;
    const UNIT_MINUTE = 3;
    const UNIT_HOUR = 5;
    const UNIT_DAY = 7;
    const UNIT_WEEK = 9;
    const UNIT_MONTH = 11;

    /**
     * Application DataMapper
     *
     * @param DataMapperInterface
     */
    private $dataMapper = null;

    /**
     * Schedule a job to run at a specific date and time
     *
     * @param string $workerName The unique name of the worker to schedule
     * @param DateTime $execute Specific time to execute in the future
     * @param array $data Data to pass to the job when run
     * @return int Scheduled id
     */
    public function scheduleAtTime($workerName, DateTime $execute, array $data=[])
    {
        return $this->dataMapper->saveScheduledJob($workerName, $execute, $data);
    }

    /**
     * Schedule a job to run at a specific interval
     *
     * @param string $workerName
     * @param int $unit One of self::UNIT_*
     * @param int $interval How many $units to wait between runs
     * @param array $data Data to pass to the job when run
     */
    public function scheduleAtInterval($workerName, $unit, $interval, array $data=[])
    {
        return $this->dataMapper->saveScheduledJob($workerName, $execute, $data);
    }

    /**
     * Get scheduled jobs up to now or a specific data if passed
     *
     * @param DateTime|null $toDate If null then 'now' will be used to get jobs that should run now
     * @return array(array('id'=>Unique id of scheduled task, 'worker_name'=>the worker to run,'job_data'=>array))
     */
    public function getScheduledToRun(DateTime $toDate = null)
    {
    }

    /**
     * Mark a job as complete which may flag or delete the task depending on the type
     *
     * @param int $scheduledId
     */
    public function markCompleted($scheduledId)
    {
    }
}