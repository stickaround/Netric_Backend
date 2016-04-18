<?php
namespace NetricTest\WorkerMan\Queue;

use Netric\WorkerMan\Queue\Gearman;

class GearmanTest extends AbstractQueueTests
{
    /**
     * Construct a job queue
     *
     * @return QueueInterface
     */
    protected function getQueue()
    {
        return new Gearman("localhost");
    }
}