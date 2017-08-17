<?php
namespace Netric\WorkerMan\Scheduler;

use DateTime;
use Netric\Db\Pgsql;
use RuntimeException;
use InvalidArgumentException;

/**
 * A Pgsql DataMapper that stores all the data in the main application database.
 * Later we may want to move this to an isolated datababse or possibly a different
 * storage interface. In some cases, if we end up using a queue that supports scheduled
 * tasks like rabbitMQ, then we might not even use a separate datastore
 * (this is all future talk of course)
 */
class SchedulerDataMapperPgsql implements SchedulerDataMapperInterface
{
    /**
     * Database where the tables reside
     *
     * @var DbInterface|null
     */
    private $db = null;

    /**
     * Used to format DateTime to strings
     */
    const DATE_FORMAT = "Y-m-d H:i:s T";

    /**
     * PgsqlSchedulerDataMapper constructor.
     *
     * @param Pgsql $applicationDbb Handle to database for managing job state
     */
    public function __construct(Pgsql $applicationDbb)
    {
        $this->db = $applicationDbb;
    }

    /**
     * Save a scheduled job and returns the ID
     *
     * @param ScheduledJob $job
     * @return int $id of saved job on success
     */
    public function saveScheduledJob(ScheduledJob $job)
    {
        $timeExecuted = ($job->getTimeExecuted()) ? $job->getTimeExecuted()->format(self::DATE_FORMAT) : null;
        $timeToExecute = ($job->getExecuteTime()) ? $job->getExecuteTime()->format(self::DATE_FORMAT) : null;

        if (!$job->getId()) {
            $sql = "INSERT INTO worker_scheduled_jobs(" .
                        "worker_name, job_data, ts_entered, " .
                        "ts_executed, ts_execute, recurrence_id" .
                   ") VALUES(" .
                        "'" . $this->db->escape($job->getWorkerName()) . "'," .
                        "'" . $this->db->escapeBytea(json_encode($job->getJobData())) . "'," .
                        "'now', " .
                        $this->db->escapeDate($timeExecuted) . "," .
                        $this->db->escapeDate($timeToExecute) . "," .
                        $this->db->escapeNumber($job->getRecurrenceId()) .

                   "); select currval('worker_scheduled_jobs_id_seq') as id;";
        } else {

            $sql = "UPDATE worker_scheduled_jobs SET
                        worker_name='" . $this->db->escape($job->getWorkerName()) . "', 
                        job_data='" . $this->db->escapeBytea(json_encode($job->getJobData())) . "', 
                        ts_executed=" . $this->db->escapeDate($timeExecuted) . ",
                        ts_execute=" . $this->db->escapeDate($timeToExecute) . ",
                        recurrence_id=". $this->db->escapeNumber($job->getRecurrenceId()) . "
                    WHERE id=" . $this->db->escapeNumber($job->getId());
        }

        // Execute the query
        $result = $this->db->query($sql);

        // Failure should never happen so die with an exception if it does
        if (!$result) {
            throw new RuntimeException(
                "Could not update scheduled job: " . $this->db->getLastError()
            );
        }

        // If we just inserted the job then set the job ID
        if (!$job->getId()) {
            $row = $this->db->getRow($result);
            $job->setId($row['id']);
        }

        return $job->getId();
    }

    /**
     * Get a scheduled job by id
     *
     * @param int $scheduledJobId
     * @return ScheduledJob
     */
    public function getScheduledJob($scheduledJobId)
    {
        $sql = "SELECT worker_name, job_data, ts_entered, 
                ts_executed, ts_execute, recurrence_id
                FROM worker_scheduled_jobs
                WHERE id=" . $this->db->escapeNumber($scheduledJobId);
        $result = $this->db->query($sql);

        // Failure should never happen so die with an exception if it does
        if (!$result) {
            throw new RuntimeException(
                "Could not update scheduled job: " . $this->db->getLastError()
            );
        }

        if ($this->db->getNumRows($result)) {
            $row = $this->db->getRow($result);
            $scheduledJob = $this->createScheduledJobFromRow($row);
            return $scheduledJob;
        }

        return null;
    }

    /**
     * Delete a scheduled job from the database
     *
     * @param ScheduledJob $job The job to delete
     * @return bool true on success, false on failure
     * @throws InvalidArgumentException If the $job does not have an ID
     */
    public function deleteScheduledJob(ScheduledJob $job)
    {
        if (!$job->getId()) {
            throw new InvalidArgumentException("Cannot delete a job without an ID");
        }

        $sql = "DELETE FROM worker_scheduled_jobs WHERE id=" . $job->getId();

        // Run the query and return true on success or false on failure
        return ($this->db->query($sql)) ? true : false;
    }

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
     * @param DateTime $toDate
     * @return ScheduledJob[]
     */
    public function getQueuedScheduledJobs(DateTime $toDate = null)
    {
        $jobsToReturn = [];

        if (!$toDate) {
            $toDate = new DateTime();
        }

        $sql = "SELECT 
                    id, worker_name, job_data, ts_entered, 
                    ts_executed, ts_execute, recurrence_id
                FROM 
                    worker_scheduled_jobs
                WHERE 
                    ts_executed IS NULL 
                    AND ts_execute <= '" . $toDate->format(self::DATE_FORMAT) . "'
                ORDER BY ts_execute LIMIT 1000";

        $result = $this->db->query($sql);
        $num = $this->db->getNumRows($result);
        for ($i = 0; $i < $num; $i++) {
            $row = $this->db->getRow($result, $i);
            $jobsToReturn[] = $this->createScheduledJobFromRow($row);
        }

        return $jobsToReturn;
    }

    /**
     * Save a recurring job to the scheduler
     *
     * @param RecurringJob $recurringJob The job that will be executing at an interval
     * @return int $id of the recurrence
     */
    public function saveRecurringJob(RecurringJob $recurringJob)
    {
    }

    /**
     * Get a recurring job by id
     *
     * @param int $recurringJobId
     * @return RecurringJob
     */
    public function getRecurringJob($recurringJobId)
    {

    }


    /**
     * Get all recurring jobs
     *
     * @return RecurringJob[]
     */
    public function getAllRecurringJobs()
    {

    }

    /**
     * Create a new ScheduledJob and set all the data from a database row
     *
     * @param array $row Associative array of table values
     * @return ScheduledJob
     */
    private function createScheduledJobFromRow(array $row)
    {
        $scheduledJob = new ScheduledJob();

        if (isset($row['id'])) {
            $scheduledJob->setId($row['id']);
        }

        if (isset($row['worker_name'])) {
            $scheduledJob->setWorkerName($row['worker_name']);
        }

        if (isset($row['job_data'])) {
            $unescapedJsonString = $this->db->unEscapeBytea($row['job_data']);
            $scheduledJob->setJobData(json_decode($unescapedJsonString, true));
        }

        if (isset($row['ts_execute'])) {
            $scheduledJob->setExecuteTime(new DateTime($row['ts_execute']));
        }
        if (isset($row['ts_executed'])) {
            $scheduledJob->setTimeExecuted(new DateTime($row['ts_executed']));
        }
        if (isset($row['recurrence_id'])) {
            $scheduledJob->setRecurrenceId((int) $row['recurrence_id']);
        }

        return $scheduledJob;

    }
}