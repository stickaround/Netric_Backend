<?php
/**
 * Service factory for the Application datamapper
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Application;

use Netric\ServiceManager\AccountServiceLocatorInterface;
use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\Config\Config;

/**
 * Create a new Application DataMapper service
 */
class DataMapperFactory implements AccountServiceLocatorInterface
{
    /**
     * Service creation factory
     *
     * @param AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return DataMapperInterface
     */
    public function createService(AccountServiceManagerInterface $sl)
    {
        $config = $sl->get(Config::class);
        return new DataMapperPgsql($config->db->host, $config->db->sysdb, $config->db->user, $config->db->password);
    }
}
