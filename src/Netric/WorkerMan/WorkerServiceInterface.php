<?php

namespace Netric\WorkerMan;

/**
 * Service used to interact with the worker manager
 */
interface WorkerServiceInterface
{
    /**
     * Add a job to the queue and return immediately with a job handle (id)
     *
     * @param string $workerName The name of the worker to run
     * @param array $jobData Data to be passed to the job
     * @return string A unique id/handle to the queued job
     */
    public function doWorkBackground($workerName, array $jobData);

    /**
     * Queue a job to be run in x number of seconds
     *
     * @parma string $workerName The name fo the worker to run
     * @param array $jobData The payload to send to the worker
     * @param int $delayedSecond The number of seconds the job will be delayed before running
     */
    public function doWorkDelayed(string $workerName, array $jobData, int $delayedSeconds): void;

    /**
     * Do work at an interval
     *
     * @param string $workerName
     * @param array $jobData
     * @param int $intervalSeconds
     * @return void
     */
    public function doWorkEvery(string $workerName, array $jobData, int $intervalSeconds): void;

    /**
     * Process the job queue or wait for new jobs
     *
     * @deprecated We no longer use this since we integrated jobqueue.svc
     * @return bool true on success, false on a failure
     */
    public function processJobQueue();

    /**
     * Process a single job
     *
     * @param string $workerName
     * @param array $payload
     * @return bool
     */
    public function processJob(string $workerName, array $payload): bool;
}
