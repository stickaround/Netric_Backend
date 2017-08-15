<?php
namespace Netric\WorkerMan\Scheduler;

use DateTime;

/**
 * Class RecurringJob represents a recurring patterns for jobs to run at regular intervals
 */
class RecurringJob extends AbstractScheduledJob
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
     * Interval unit like minute, hour, day, month...
     *
     * @var int
     */
    private $unit = self::UNIT_DAY;

    /**
     * Recurs interval of $this->>units
     *
     * @var int
     */
    private $interval = 0;

    /**
     * Set the interval unit (minutes, days, weeks, etc...)
     *
     * @param int $unit A unit constant from self::UNIT_*
     */
    public function setIntervalUnit($unit)
    {
        $this->unit = $unit;
    }

    /**
     * Get the interval unit for this recurrence
     *
     * @return int
     */
    public function getIntervalUnit()
    {
        return $this->unit;
    }

    /**
     * Set the interval for recurring
     *
     * Example: If we were to run this job every 30 days, the interval
     * would be 30 and the interval unit would be self::UNIT_DAY
     *
     * @param $interval
     */
    public function setInterval($interval)
    {
        $this->interval = $interval;
    }

    /**
     * Get the interval for this recurrence
     *
     * @see RecurringJob::setInterval for more information on how the interval works
     *
     * @return int
     */
    public function getInterval()
    {
        return $this->interval;
    }
}