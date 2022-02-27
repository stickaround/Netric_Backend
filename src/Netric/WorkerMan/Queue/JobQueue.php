<?php

declare(strict_types=1);

namespace Netric\WorkerMan\Queue;

use Netric\WorkerMan\WorkerFactory;
use Netric\WorkerMan\WorkerInterface;
use JobQueueApi\JobClient;
use JobQueueApiFactory\JobQueueApiFactory;

/**
 * New queue that uses the aereus jobqueue service
 */
class JobQueue implements QueueInterface
{
    /**
     * Workers that are listening for jobs by workerName
     *
     * @var WorkerInterface[]
     */
    private array $listeners = [];

    /**
     * JobQueue client interface
     */
    private JobClient $jobQueue;

    /**
     * Host of the server to connect to
     */
    private string $server = "";

    /**
     * Class constructor
     *
     * @param WorkerFactory $workerFactory
     */
    public function __construct(string $server)
    {
        $this->server = $server;
    }

    /**
     * Add a job to the queue and run it right away
     *
     * With other queues it puts it in the background, but for in-memory
     * doWork and doWorkBackground are essentially the same thing.
     *
     * @param string $workerName The name of the worker to run
     * @param array $jobData Data to be passed to the job
     * @return string A unique id/handle to the queued job
     */
    public function doWorkBackground($workerName, array $jobData)
    {
        $factory = new JobQueueApiFactory();
        $client = $factory->createJobQueueClient(gethostbyname($this->server));
        $success = $client->run($workerName, json_encode($jobData));
        return ($success) ? "1" : "0";
    }

    /**
     * Add a job to the queue and run it after delayed number of seconds
     *
     * With other queues it puts it in the background, but for in-memory
     * doWork and doWorkBackground are essentially the same thing.
     *
     * @param string $workerName The name of the worker to run
     * @param array $jobData Data to be passed to the job
     * @return string A unique id/handle to the queued job
     */
    public function doWorkBackgroundDelayed(string $workerName, array $jobData, int $delayInSeconds)
    {
        $factory = new JobQueueApiFactory();
        $client = $factory->createJobQueueClient(gethostbyname($this->server));
        $success = $client->runDelayed($workerName, json_encode($jobData), $delayInSeconds);
        return ($success) ? "1" : "0";
    }

    /**
     * Add an available worker to the queue
     *
     * @param string $workerName The name of the worker to run
     * @param WorkerInterface $worker Will call $worker::work and must match $workerName queue
     * @return bool true on success, false on failure
     */
    public function addWorker($workerName, WorkerInterface $worker)
    {
        $this->listeners[$workerName] = $worker;
    }

    /**
     * Get all workers that are listening for jobs
     *
     * @return WorkerInterface[]
     */
    public function getWorkers()
    {
        return $this->listeners;
    }

    /**
     * Loop through the work queue and dispatch each job to the appropriate worker (pop)
     *
     * @return bool true on success, false if there were no jobs to run
     */
    public function dispatchJobs()
    {
        // Not implemented
        return false;
    }

    /**
     * Remove all jobs in a a worker queue
     *
     * @param string $workerName The name of the queue to clear
     * @return int number of jobs cleared
     */
    public function clearJobQueue($workerName)
    {
        // Not implemented
        return 0;
    }
}
