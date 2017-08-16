<?php
namespace Netric\Db;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Config\Config;

/**
 * Create a Db service for the application - not account specific
 */
class ApplicationDbFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $sl ServiceLocator for injecting dependencies
     * @return DbInterface
     */
    public function createService(ServiceLocatorInterface $sl)
    {
        $config = $sl->get(Config::class);
        return new Pgsql(
            $config->db->host,
            $config->db->sysdb,
            $config->db->user,
            $config->db->password
        );
    }
}
