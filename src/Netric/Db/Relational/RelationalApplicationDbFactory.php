<?php
namespace Netric\Db\Relational;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Config\Config;

/**
 * Create a service that returns a handle to an application (not account) database
 */
class RelationalApplicationDbFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return RelationalDbInterface
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get(Config::class);

        return new PgsqlDb(
            $config->db["host"],
            $config->db['sysdb'],
            $config->db["user"],
            $config->db["password"]
        );
    }
}
