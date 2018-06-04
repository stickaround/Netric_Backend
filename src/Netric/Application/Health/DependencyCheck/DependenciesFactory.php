<?php
namespace Netric\Application\Health\DependencyCheck;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Config\Config;
use Netric\Application\Health\DependencyCheck\PgsqlDependencyCheck;

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
        $config = $serviceLocator->get(Config::class);

        // We need pgsql to be working
        $dependencies[] = new PgsqlDependencyCheck(
            $config->db->host,
            $config->db->user,
            $config->db->password
        );

        // Mogile must be active
        // $dependencies[] = new MogileFsDependencyCheck(
        //     $config->files->server,
        //     $config->files->account,
        //     $config->files->port
        // );

        return $dependencies;
    }
}
