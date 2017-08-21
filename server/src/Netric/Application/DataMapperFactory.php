<?php
/**
 * Service factory for the Application datamapper
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Application;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;

use Netric\Config\Config;

/**
 * Create a new Application DataMapper service
 */
class DataMapperFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $sl ServiceLocator for injecting dependencies
     * @return DataMapperInterface
     */
    public function createService(ServiceLocatorInterface $sl)
    {
        $config = $sl->get(Config::class);
        return new DataMapperPgsql($config->db->host, $config->db->sysdb, $config->db->user, $config->db->password);
    }
}
