<?php
namespace Netric\WorkerMan\Scheduler;

use DateTime;

/**
 * Class AbstractScheduledJob
 *
 * Base functionality for a job that is getting scheduled.
 * This will be used to define common methods and properties for both
 * recurring and scheduled jobs.
 *
 * @package Netric\WorkerMan\Scheduler
 */
abstract class AbstractScheduledJob
{
    /**
     * The unique ID of the record if previously saved
     *
     * @var int
     */
    protected $id = null;

    /**
     * The name of the worker to set
     *
     * @var string
     */
    protected $workerName = "";

    /**
     * Data to send to the job when we execute
     *
     * The job queue calls this the workload and will send it to the
     * job when it executes.
     *
     * @var array
     */
    protected $jobData = [];

    /**
     * Date and time when the job was run
     *
     * If this is a recurring job then this value represents the
     * date and time when an instance of the recurrence was last run.
     *
     * @var DateTime
     */
    protected $timeExecuted = null;

    /**
     * Set the unique id of this job
     *
     * The ID can either be of a recurrence or a scheduled job
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get the unique ID of the scheduled job or recurrence id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the worker name for this job
     *
     * @param string $workerName
     */
    public function setWorkerName($workerName)
    {
        $this->workerName = $workerName;
    }

    /**
     * Get the worker name for this job
     *
     * @return string
     */
    public function getWorkerName()
    {
        return $this->workerName;
    }

    /**
     * Set data (workload) to be sent to the job when it executes
     *
     * This data is stored as an array but must contain only
     * scalar data types since it will be serialized and
     * un-serialized to save it and to send it to the job queue.
     *
     * @param array $jobData
     */
    public function setJobData(array $jobData)
    {
        $this->jobData = $jobData;
    }

    /**
     * Get the data (workload) that will be sent with to the job when executed
     *
     * @return array
     */
    public function getJobData()
    {
        return $this->jobData;
    }

    /**
     * Set the last date and time when the job or job instance was run
     *
     * @param DateTime $timeExecuted
     */
    public function setTimeExecuted(DateTime $timeExecuted)
    {
        $this->timeExecuted = $timeExecuted;
    }

    /**
     * Get the last date and time when the job or job instance was run
     *
     * @return DateTime
     */
    public function getTimeExecuted()
    {
        return $this->timeExecuted;
    }
}