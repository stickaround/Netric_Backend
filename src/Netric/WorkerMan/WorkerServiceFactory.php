<?php

/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */

namespace Netric\WorkerMan;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Config\ConfigFactory;
use Netric\WorkerMan\Queue\Gearman;
use Netric\WorkerMan\Queue\InMemory;
use RuntimeException;

/**
 * Handle setting up a worker service
 */
class WorkerServiceFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return WorkerService
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $config = $serviceLocator->get(ConfigFactory::class);
        $workerFactory = new WorkerFactory($serviceLocator);

        $queue = null;

        switch ($config->workers->queue) {
            case 'gearman':
                $queue = new Gearman($config->workers->server);
                break;
            case 'memory':
                $queue = new InMemory($workerFactory);
                break;
            default:
                throw new RuntimeException("Worker queue not supported: " . $config->workers->queue);
                break;
        }

        return new WorkerService($serviceLocator->getApplication(), $queue, $workerFactory);
    }
}
