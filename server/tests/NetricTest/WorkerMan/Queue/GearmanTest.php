<?php
namespace NetricTest\WorkerMan\Queue;

use Netric\WorkerMan\Queue\Gearman;
use Netric\WorkerMan\Queue\QueueInterface;

class GearmanTest extends AbstractQueueTests
{
    protected function setUp()
    {
        /*
         * These tests only run in local development mode since docker in centos (testing)
         * model cannot handle threads for gearman.
         */
        if (getenv("APPLICATION_ENV") != "development") {
            $this->markTestSkipped("Gearman threading only works in docker on debian for some reason");
        }

        parent::setUp();
    }

    /**
     * Construct a job queue
     *
     * @return QueueInterface
     */
    protected function getQueue()
    {
        $config = $this->account->getServiceManager()->get('Netric\Config\Config');
        return new Gearman($config->workers->server);
    }
}
