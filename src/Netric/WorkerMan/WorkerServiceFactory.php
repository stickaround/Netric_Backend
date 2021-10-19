<?php

/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */

namespace Netric\WorkerMan;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Config\ConfigFactory;
use Netric\WorkerMan\Queue\Gearman;
use Netric\WorkerMan\Queue\InMemory;
use RuntimeException;

/**
 * Handle setting up a worker service
 */
class WorkerServiceFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return WorkerService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
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

        return new WorkerService($queue, $workerFactory);
    }
}
