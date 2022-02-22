<?php

namespace Netric\Application\Health\DependencyCheck;

use JobQueueApiFactory\JobQueueApiFactory;
use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Config\ConfigFactory;

/**
 * Create a service that returns a handle to an application (not account) database
 */
class DependenciesFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return array of DependencyCheckInterface
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $dependencies = [];
        $config = $serviceLocator->get(ConfigFactory::class);

        // We need pgsql to be working
        $dependencies[] = new PgsqlDependencyCheck(
            $config->db->host,
            $config->db->user,
            $config->db->password
        );

        // Add the jobqueue since we need it to run background jobs
        $clientFactory = new JobQueueApiFactory();
        $apiClient = $clientFactory->createJobQueueClient($config->workers->server)
        $dependencies[] = new JobQueueDependencyCheck($apiClient);

        return $dependencies;
    }
}
