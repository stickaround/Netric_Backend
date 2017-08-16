<?php
namespace Netric\Db;

use Netric\ServiceManager\AccountServiceLocatorInterface;
use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\Config\Config;

/**
 * Create a Db service for the application - not account specific
 */
class ApplicationDbFactory implements AccountServiceLocatorInterface
{
    /**
     * Service creation factory
     *
     * @param AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return DbInterface
     */
    public function createService(AccountServiceManagerInterface $sl)
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
