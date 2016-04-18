<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\WorkerMan;

/**
 * A class that represents a single job being worked on
 */
class Job
{
    /**
     * The workload of this job
     *
     * @var array
     */
    private $workload = array();

    /**
     * Set the workload of this job
     *
     * @param array $workload
     */
    public function setWorkload(array $workload)
    {
        $this->workload = $workload;
    }

    /**
     * Get the workload of this job
     *
     * @return array
     */
    public function getWorkload()
    {
        return $this->workload;
    }
}