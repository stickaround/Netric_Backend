<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Worker;

use Netric\WorkerMan\Job;
use Netric\WorkerMan\WorkerInterface;

/**
 * This worker is used to test the WorkerMan
 */
class TestWorker implements WorkerInterface
{
    /**
     * Cache the result
     *
     * @var string
     */
    private $result = "";

    /**
     * Take a string and reverse it
     *
     * @param Job $job
     * @return mixed The reversed string
     */
    public function work(Job $job)
    {
        $workload = $job->getWorkload();
        $this->result = strrev($workload['mystring']);
        return $this->result;
    }

    /**
     * Get the results of the last job
     *
     * @return string
     */
    public function getResult()
    {
        return $this->result;
    }
}
