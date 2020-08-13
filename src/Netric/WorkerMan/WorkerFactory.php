<?php

declare(strict_types=1);

namespace Netric\WorkerMan;

use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for creating workers to be executed through the WorkerMan queue
 */
class WorkerFactory
{
    /**
     * Service manager used to load dependencies
     *
     * @var AccountServiceManagerInterface
     */
    private $serviceManager = null;

    /**
     * Class constructor
     *
     * @param AccountServiceManagerInterface $serviceLocator ServiceLocator for injecting dependencies
     */
    public function __construct(ServiceLocatorInterface $serviceLocator)
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
