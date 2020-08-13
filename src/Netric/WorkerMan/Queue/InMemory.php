<?php

declare(strict_types=1);

namespace Netric\WorkerMan\Queue;

use Netric\WorkerMan\WorkerFactory;
use Netric\WorkerMan\WorkerInterface;
use Netric\WorkerMan\Job;

/**
 * The in-memory queue is essentially just an event queue interface
 */
class InMemory implements QueueInterface
{
    /**
     * Queued jobs
     *
     * @var array
     */
    public array $queuedJobs = [];

    /**
     * Workers that are listening for jobs by workerName
     *
     * @var WorkerInterface[]
     */
    private array $listeners = [];

    /**
     * The worker factory used to load workers
     */
    private WorkerFactory $workerFactory;

    /**
     * Class constructor
     *
     * @param WorkerFactory $workerFactory
     */
    public function __construct(WorkerFactory $workerFactory)
    {
        $this->workerFactory = $workerFactory;
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
        $this->queuedJobs[] = [$workerName, $jobData];
        // Run the jobs immediately - in a real job queue, this would happen asynchronously
        $this->runJobImmediately($workerName, $jobData);
        return (string) count($this->queuedJobs);
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
        if (count($this->listeners) === 0) {
            return false;
        }

        while (true) {
            foreach ($this->queuedJobs as $aJob) {
                $workerName = $aJob[0];
                $jobData = $aJob[1];

                // Skip over jobs we are not listening to
                if (!isset($this->listeners[$workerName])) {
                    continue;
                }

                // Construct job wrapper
                $job = new Job();
                $job->setWorkload($jobData);

                // Send job to the worker
                $worker = $this->listeners[$workerName];
                $worker->work($job);
                return true;
            }
        }
    }

    /**
     * Remove all jobs in a a worker queue
     *
     * @param string $workerName The name of the queue to clear
     * @return int number of jobs cleared
     */
    public function clearJobQueue($workerName)
    {
        $toRemove = [];
        $numQueuedJobs = count($this->queuedJobs);
        for ($i = 0; $i < $numQueuedJobs; $i++) {
            if ($this->queuedJobs[$i][0] === $workerName) {
                $toRemove[] = $i;
            }
        }

        foreach ($toRemove as $idxToDelete) {
            array_splice($this->queuedJobs, $idxToDelete, 1);
        }

        return count($toRemove);
    }


    /**
     * Run the job right away, similar to an event queue
     *
     * Normally queues would be processed in the background through the WorkerService
     * but this in-memory queue will handle the jobs immediately in the same process
     * as the main code that is executing. This essentially makes it an event dispatcher.
     *
     * @param string $workerName
     * @param array $jobData
     * @return void
     */
    private function runJobImmediately(string $workerName, array $jobData): void
    {
        $worker = $this->workerFactory->getWorkerByName($workerName);
        if ($worker) {
            // Construct job wrapper
            $job = new Job();
            $job->setWorkload($jobData);
            $worker->work($job);
        }
    }
}
