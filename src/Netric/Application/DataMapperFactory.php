<?php

namespace Netric\Application;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Config\ConfigFactory;

/**
 * Create a new Application DataMapper service
 */
class DataMapperFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return DataMapperInterface
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $config = $serviceLocator->get(ConfigFactory::class);

        return new ApplicationRdbDataMapper(
            $config->db->host,
            $config->db->dbname,
            $config->db->user,
            $config->db->password
        );
    }
}
