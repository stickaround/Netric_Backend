<?php

namespace Netric\WorkerMan;

use Netric\WorkerMan\Queue\QueueInterface;
use Netric\Application\Application;
use RuntimeException;

/**
 * Service used to interact with the worker manager
 */
class WorkerService
{
    /**
     * A job queue to send jobs to an pull job info from
     *
     * @var QueueInterface
     */
    private $jobQueue = null;

    /**
     * Array of workers to handle jobs
     *
     * @var WorkerInterface[]
     */
    private $workers = null;

    /**
     * Factory for loading workers
     */
    private WorkerFactory $workerFactory;

    /**
     * Setup the WorkerService
     *
     * @param Application $application Instance of current running netric application
     * @param QueueInterface $queue The Queue used to push jobs and pull info
     */
    public function __construct(
        QueueInterface $queue,
        WorkerFactory $workerFactory
    ) {
        $this->jobQueue = $queue;
        $this->workerFactory = $workerFactory;
    }

    /**
     * Add a job to the queue and return immediately with a job handle (id)
     *
     * @param string $workerName The name of the worker to run
     * @param array $jobData Data to be passed to the job
     * @return string A unique id/handle to the queued job
     */
    public function doWorkBackground($workerName, array $jobData)
    {
        return $this->jobQueue->doWorkBackground($workerName, $jobData);
    }

    /**
     * Process the job queue or wait for new jobs
     *
     * @return bool true on success, false on a failure
     */
    public function processJobQueue()
    {
        // Make sure that we have loaded the workers
        if ($this->workers === null) {
            $this->loadWorkers();
        }

        // Wait for jobs and send them to workers
        return $this->jobQueue->dispatchJobs();
    }

    /**
     * Load up workers
     */
    private function loadWorkers()
    {
        $this->workers = [];

        // Load up all workers from the ../Worker directory
        foreach (glob(__DIR__ . "/Worker/*Worker.php") as $filename) {
            // Add each worker as a listener
            $workerName = substr(basename($filename), 0, - (strlen(".php")));
            $workerClass = __NAMESPACE__ . "\\Worker\\" . $workerName;
            $worker = $this->workerFactory->getWorkerByName($workerClass);
            if (!$worker) {
                throw new RuntimeException("Could not load worker: " . $workerClass);
            }
            $this->workers[$workerClass] = $worker;
            $this->jobQueue->addWorker($workerClass, $this->workers[$workerClass]);
        }
    }
}
