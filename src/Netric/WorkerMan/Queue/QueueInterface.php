<?php

/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\WorkerMan\Queue;

use Netric\WorkerMan\WorkerInterface;

/**
 * Define common interface functions to use with any job queue
 */
interface QueueInterface
{
    /**
     * Add a job to the queue and run it
     *
     * @param string $workerName The name of the worker to run
     * @param array $jobData Data to be passed to the job
     * @return string A unique id/handle to the queued job
     */
    public function doWorkBackground($workerName, array $jobData);

    /**
     * Do a job in x number of seconds
     *
     * @param string $workerName
     * @param array $jobData
     * @param int $delayInSeconds
     * @return void
     */
    public function doWorkBackgroundDelayed(string $workerName, array $jobData, int $delayInSeconds);

    /**
     * Add an available worker to the queue
     *
     * @param string $workerName The name of the worker to run
     * @param WorkerInterface $worker Will call $worker::work and must match $workerName queue
     * @return bool true on success, false on failure
     */
    public function addWorker($workerName, WorkerInterface $worker);

    /**
     * Get all workers that are listening for jobs
     *
     * @return WorkerInterface[]
     */
    public function getWorkers();

    /**
     * Remove all jobs in a a worker queue
     *
     * @param string $workerName The name of the queue to clear
     * @return int number of jobs cleared
     */
    public function clearJobQueue($workerName);

    /**
     * Loop through the work queue and dispatch each job to the appropriate worker (pop)
     *
     * @return bool true on success, false if there were no jobs to run
     */
    public function dispatchJobs();
}
