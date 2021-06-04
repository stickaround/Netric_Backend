<?php

namespace Netric\Db\Relational;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Config\ConfigFactory;

/**
 * Create a service that returns a handle to an account database
 */
class RelationalDbFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return RelationalDbInterface
     */
    public function __invoke(ServiceContainerInterface $sl)
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
