<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\WorkerMan;

use Netric\WorkerMan\Queue\QueueInterface;

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
     * Setup the WorkerService
     *
     * @param QueueInterface $queue The Queue used to push jobs and pull info
     */
    public function __construct(QueueInterface $queue)
    {
        $this->jobQueue = $queue;
    }

    /**
     * Add a job to the queue and run it
     *
     * @param string $workerName The name of the worker to run
     * @param array $jobData Data to be passed to the job
     * @return mixed Whatever the result of the worker is
     */
    public function doWork($workerName, array $jobData)
    {
        return $this->jobQueue->doWork($workerName, $jobData);
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
     * Schedule a job to run in the background at a future time
     *
     * @param string $workerName The name of the worker to run
     * @param array $jobData Any data passed to the worker
     * @param \DateTime $timeStart The time when the job should start
     */
    public function scheduleWork($workerName, array $jobData, \DateTime $timeStart)
    {
        // TODO: we have to figure this out
    }

    /**
     * Process the job queue or wait for new jobs
     *
     * @return bool true on sucess, false on a failure
     */
    public function processJobQueue()
    {

    }
}
