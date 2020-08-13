<?php

namespace NetricTest\WorkerMan\Queue;

use Netric\WorkerMan\Queue\InMemory;
use Netric\WorkerMan\WorkerFactory;

class InMemeoryTest extends AbstractQueueTests
{
    /**
     * Construct a job queue
     *
     * @return QueueInterface
     */
    protected function getQueue()
    {
        $workerFactoryMock = $this->createMock(WorkerFactory::class);
        return new InMemory($workerFactoryMock);
    }
}
