<?php

namespace Netric\Controller;

use Netric\Mvc\ControllerFactoryInterface;
use Netric\Mvc\ControllerInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Application\Health\HealthCheckFactory;
use Netric\Log\LogFactory;

/**
 * Construct the FilesControllerFactory for interacting with email messages
 */
class HealthControllerFactory implements ControllerFactoryInterface
{
    /**
     * Construct a controller and return it
     *
     * @param ServiceContainerInterface $serviceLocator
     * @return ControllerInterface
     */
    public function get(ServiceContainerInterface $serviceLocator): ControllerInterface
    {
        $healthCheck = $serviceLocator->get(HealthCheckFactory::class);
        $log = $serviceLocator->get(LogFactory::class);

        return new HealthController(
            $healthCheck,
            $log
        );
    }
}
