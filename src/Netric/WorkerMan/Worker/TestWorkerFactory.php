<?php

declare(strict_types=1);

namespace Netric\WorkerMan\Worker;

use Aereus\ServiceContainer\ServiceContainerInterface;

/**
 * Construct worker called after each entity save
 */
class TestWorkerFactory
{
    /**
     * Entity creation factory
     *
     * @param ServiceContainerInterface $serviceLocator For injecting dependencies
     * @return TestWorker
     */
    public function create(ServiceContainerInterface $serviceLocator)
    {
        return new TestWorker($serviceLocator->getApplication());
    }
}
