<?php
namespace NetricTest\WorkerMan\Queue;

use Netric\WorkerMan\Queue\Gearman;
use Netric\WorkerMan\Queue\QueueInterface;

class GearmanTest extends AbstractQueueTests
{
    /**
     * Construct a job queue
     *
     * @return QueueInterface
     */
    protected function getQueue()
    {
        $config = $this->account->getServiceManager()->get('Netric\Config\Config');
        echo "\nConnecting to " . $config->workers->server . "\n";
        return new Gearman($config->workers->server);
    }
}
