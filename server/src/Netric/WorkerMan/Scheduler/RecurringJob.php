<?php
namespace Netric\WorkerMan\Scheduler;

use DateTime;
use DateInterval;

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

    /**
     * Get the date and time when this recurring pattern should execute next
     *
     * @return DateTime The exact date and time when the next job should execute
     */
    public function getNextExecuteTime()
    {
        $nextExecuteTime = new DateTime();

        $lastExecuted = $this->getTimeExecuted();

        // If the recurrence has never started, then trigger the job now
        if ($lastExecuted === null) {
            // Set the next execute time to way in the past to assure it runs
            $nextExecuteTime->sub(new DateInterval("P1Y"));
            return $nextExecuteTime;
        } else {
            // Start with the last execution time
            $nextExecuteTime = $lastExecuted;
        }

        // Construct the prefix and postfix based on the interval unit unit
        switch ($this->getIntervalUnit()) {
            case RecurringJob::UNIT_MINUTE:
                $intervalSpecPostfix = "M";
                $intervalSpecPrefix = "PT"; // Period & Time
                break;
            case RecurringJob::UNIT_HOUR:
                $intervalSpecPostfix = "H";
                $intervalSpecPrefix = "PT"; // Period & Time
                break;
            case RecurringJob::UNIT_DAY:
                $intervalSpecPostfix = "D";
                $intervalSpecPrefix = "P"; // Period only
                break;
            case RecurringJob::UNIT_MONTH:
                $intervalSpecPostfix = "D";
                $intervalSpecPrefix = "P"; // Period only
                break;
            default:
                throw new \RuntimeException(
                    "Interval unit not known: " . $this->getIntervalUnit()
                );

        }

        // Add the interval to the last executed date and return it as the next execute time
        $intervalSpec = $intervalSpecPrefix . $this->getInterval() . $intervalSpecPostfix;
        $nextExecuteTime->add(new DateInterval($intervalSpec));

        return $nextExecuteTime;
    }
}