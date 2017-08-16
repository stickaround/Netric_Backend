<?php
namespace Netric\WorkerMan\Scheduler;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Create a datamapper service for the job scheduler
 */
class SchedulerDataMapperFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $sl ServiceLocator for injecting dependencies
     * @return SchedulerDataMapperInterface
     */
    public function createService(ServiceLocatorInterface $sl)
    {
        $applicationDb = $sl->get('Netric/Db/ApplicationDb');
        return new PgsqlSchedulerDataMapper($applicationDb);
    }
}
