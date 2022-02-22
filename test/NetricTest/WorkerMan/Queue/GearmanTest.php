<?php

namespace NetricTest\WorkerMan\Queue;

use Netric\WorkerMan\Queue\Gearman;
use Netric\WorkerMan\Queue\QueueInterface;
use Netric\Config\ConfigFactory;
use Netric\WorkerMan\WorkerFactory;

class GearmanTest extends AbstractQueueTests
{
    /**
     * Construct a job queue
     *
     * @return QueueInterface
     */
    protected function getQueue()
    {
        $config = $this->account->getServiceManager()->get(ConfigFactory::class);
        return new Gearman($config->workers->worker_gearman);
    }
}
