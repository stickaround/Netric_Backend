<?php

namespace Netric\Application\Health\DependencyCheck;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Config\ConfigFactory;

/**
 * Create a service that returns a handle to an application (not account) database
 */
class DependenciesFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return array of DependencyCheckInterface
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $dependencies = [];
        $config = $serviceLocator->get(ConfigFactory::class);

        // We need pgsql to be working
        $dependencies[] = new PgsqlDependencyCheck(
            $config->db->host,
            $config->db->user,
            $config->db->password
        );

        return $dependencies;
    }
}
