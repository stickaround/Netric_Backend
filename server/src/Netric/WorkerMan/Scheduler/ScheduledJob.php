<?php
namespace Netric\WorkerMan\Scheduler;

use DateTime;

class ScheduledJob extends AbstractScheduledJob
{
    /**
     * Date and time when this job should run
     *
     * @var DateTime
     */
    protected $executeTime = null;

    /**
     * ID of the recurring job if this is an instance in a series
     *
     * @var int
     */
    private $recurrenceId = null;

    /**
     * Set the date and time when this job will execute
     *
     * @param DateTime $executeTime
     */
    public function setExecuteTime(DateTime $executeTime)
    {
        $this->executeTime = $executeTime;
    }

    /**
     * Get the date and time when this job will execute
     *
     * @return DateTime
     */
    public function getExecuteTime()
    {
        return $this->executeTime;
    }

    /**
     * Set the unique id of the recurring job if this is part of a series of repeating jobs
     *
     * @param int $id
     */
    public function setRecurrenceId($id)
    {
        $this->recurrenceId = $id;
    }

    /**
     * Get ID of recurrence if part of a series of repeating jobs
     *
     * @return int
     */
    public function getRecurrenceId()
    {
        return $this->recurrenceId;
    }
}