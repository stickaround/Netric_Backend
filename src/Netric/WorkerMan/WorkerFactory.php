<?php

declare(strict_types=1);

namespace Netric\WorkerMan;

use Aereus\ServiceContainer\ServiceContainerInterface;

/**
 * Factory for creating workers to be executed through the WorkerMan queue
 */
class WorkerFactory
{
    /**
     * ServiceLocator for injecting dependencies
     *
     * @var ServiceContainerInterface
     */
    private $serviceManager = null;

    /**
     * Class constructor
     *
     * @param ServiceContainerInterface $serviceLocator ServiceLocator for injecting dependencies
     */
    public function __construct(ServiceContainerInterface $serviceLocator)
    {
        $this->serviceManager = $serviceLocator;
    }

    /**
     * Get a new instance of a worker based on the class name
     *
     * @param string $className
     * @return WorkerInterface|null
     */
    public function getWorkerByName(string $className): ?WorkerInterface
    {
        $factoryClassName = $className . 'Factory';
        if (class_exists($factoryClassName)) {
            $workerFactory = new $factoryClassName();
            return $workerFactory->create($this->serviceManager);
        }

        return null;
    }
}
