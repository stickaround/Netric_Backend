<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\WorkerMan\Queue;

use Netric\WorkerMan\WorkerInterface;
use Netric\WorkerMan\Job;

class Gearman implements QueueInterface
{
    /**
     * Gearman client
     *
     * var \GearmanClient
     */
    private $gmClient = null;

    /**
     * Gearman worker
     *
     * @var \GearmanWorker
     */
    private $gmWorker = null;

    /**
     * Array of listeners
     *
     * @var WorkerInterface[]
     */
    private $listeners = array();

    /**
     * Initialize a Gearman job queue
     *
     * @param $server
     */
    public function __construct($server)
    {
        $this->gmClient = new \GearmanClient();
        $this->gmClient->addServer($server);

        $this->gmWorker = new \GearmanWorker();
        $this->gmWorker->addServer($server);

        // Turn off blocking so that $this->gmWorker->work will return right away if no jobs
        $this->gmWorker->setOptions(GEARMAN_WORKER_NON_BLOCKING);

        /*
        $client->setCreatedCallback("create_change");

        $client->setDataCallback("data_change");

        $client->setStatusCallback("status_change");

        $client->setCompleteCallback("complete_change");

        $client->setFailCallback("fail_change");

        echo "DONE\n";

        function create_change($task)
        {
            echo "CREATED: " . $task->jobHandle() . "\n";
        }

        function status_change($task)
        {
            echo "STATUS: " . $task->jobHandle() . " - " . $task->taskNumerator() .
                "/" . $task->taskDenominator() . "\n";
        }

        function complete_change($task)
        {
            echo "COMPLETE: " . $task->jobHandle() . ", " . $task->data() . "\n";
        }

        function fail_change($task)
        {
            echo "FAILED: " . $task->jobHandle() . "\n";
        }

        function data_change($task)
        {
            echo "DATA: " . $task->data() . "\n";
        }
        Function Client_error()
        {
            if (! $client->runTasks())
                return $client->error() ;
        }
        */
    }

    /**
     * Add a job to the queue and wait for it to return (RPC)
     *
     * @param string $workerName The name of the worker to run
     * @param array $jobData Data to be passed to the job
     * @return mixed Whatever the result of the worker is
     */
    public function doWork($workerName, array $jobData)
    {
        return $this->gmClient->doNormal($workerName, json_encode($jobData));
    }

    /**
     * Add a job to the queue and run it
     *
     * @param string $workerName The name of the worker to run
     * @param array $jobData Data to be passed to the job
     * @return string A unique id/handle to the queued job
     */
    public function doWorkBackground($workerName, array $jobData)
    {
        $ret = $this->gmClient->doBackground($workerName, json_encode($jobData));

        if ($this->gmClient->returnCode() != GEARMAN_SUCCESS) {
            throw new \RuntimeException("Cannot run background job: " . $this->gmClient->error());
        }

        return $ret;
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
        $this->gmWorker->addFunction($workerName, array($this, "sendJobToWorker"));
    }

    /**
     * Array of listeners with the key bing the WorkerName
     *
     * @return \Netric\WorkerMan\WorkerInterface[]
     */
    public function getWorkers()
    {
        return $this->listeners;
    }

    /**
     * Loop through the work queue and dispatch each job to the appropriate worker (pop)
     *
     * @return bool true on success, false on failure
     */
    public function dispatchJobs()
    {
        if (!count($this->getWorkers())) {
            return false;
        }

        if ($this->gmWorker->work()) {
            return true;
        } else {
            $error = $this->gmWorker->error();
            if ($error) {
                throw new \RuntimeException("Job failed: " . $error);
            } else {
                // No jobs
                return false;
            }
        }
    }

    /**
     * Local listener called when gearman submits a job
     *
     * @param \GearmanJob $gmJob
     * @return mixed Results of job
     */
    public function sendJobToWorker(\GearmanJob $gmJob)
    {
        if (!isset($this->listeners[$gmJob->functionName()])) {
            throw new \RuntimeException("No listeners for job: " . $gmJob->functionName());
        }

        // Construct job wrapper
        $job = new Job();
        $job->setWorkload(json_decode($gmJob->workload(), true));
        // TODO: populate the job with whatever is needed here

        // Send job to the worker
        $worker = $this->listeners[$gmJob->functionName()];
        return $worker->work($job);
    }

    /**
     * Remove all jobs in a a worker queue
     *
     * @param string $workerName The name of the queue to clear
     * @return int number of jobs cleared
     */
    public function clearWorkerQueue($workerName)
    {
        $purged = 0;

        // First unregister all listeners with gearman client
        if (count($this->getWorkers())) {
            $this->gmWorker->unregisterAll();
        }

        // Register a no-op function to run through the queue (our /dev/null)
        $this->gmWorker->addFunction($workerName, function($job) { return true; });


        // If there are no jobs work will return GEARMAN_NO_JOBS
        while($this->dispatchJobs()) {
            $purged++;
        }
        $this->gmWorker->unregister($workerName);

        // Restore options
        //$this->gmWorker->removeOptions(GEARMAN_WORKER_NON_BLOCKING);

        // Re-register original listeners
        foreach ($this->listeners as $workerName=>$worker) {
            $this->gmWorker->addFunction($workerName, array($this, "sendJobToWorker"));
        }

        return $purged;
    }
}
