<?php
namespace Netric\WorkerMan;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Config\Config;

/**
 * Handle setting up a job scheduler service
 */
class SchedulerServiceFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $sl ServiceLocator for injecting dependencies
     * @return SchedulerService
     */
    public function createService(ServiceLocatorInterface $sl)
    {
        $config = $sl->get(Config::class);

        $queue = null;

        switch ($config->workers->queue) {
            case 'gearman':
                $queue = new Queue\Gearman($config->workers->server);
                break;
            default:
                throw new \RuntimeException("Worker queue not supported: " . $config->workers->queue);
                break;
        }

        $schedulerService = $sl->get(SchedulerService::class);

        return new WorkerService($sl->getApplication(), $queue, $schedulerService);
    }
}
