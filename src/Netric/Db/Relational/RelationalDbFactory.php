<?php

namespace Netric\Db\Relational;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Config\ConfigFactory;

/**
 * Create a service that returns a handle to an account database
 */
class RelationalDbFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $sl ServiceLocator for injecting dependencies
     * @return RelationalDbInterface
     */
    public function createService(ServiceLocatorInterface $sl)
    {
        $config = $sl->get(ConfigFactory::class);

        $db = new PgsqlDb(
            $config->db["host"],
            $config->db["dbname"],
            $config->db["user"],
            $config->db["password"]
        );

        return $db;
    }
}
