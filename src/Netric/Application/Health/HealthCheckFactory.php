<?php
namespace Netric\Application\Health;

use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\Log\LogFactory;
use Netric\Application\Health\DependencyCheck\DependenciesFactory;

/**
 * Construct an application health service
 */
class HealthCheckFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator For loading dependencies
     * @return HealthCheck
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $log = $serviceLocator->get(LogFactory::class);
        $dependencies = $serviceLocator->get(DependenciesFactory::class);
        return new HealthCheck($log, $dependencies);
    }
}
