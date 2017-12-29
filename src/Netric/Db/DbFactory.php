<?php
/**
 * Service factory for the Netric Db
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Db;

use Netric\ServiceManager\AccountServiceLocatorInterface;
use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\Config\Config;

/**
 * Create a service that returns a handle to an account database
 */
class DbFactory implements AccountServiceLocatorInterface
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

        $db = new Pgsql(
            $config->db["host"],
            $sl->getAccount()->getDatabaseName(),
            $config->db["user"],
            $config->db["password"]
        );

        // Set the schema to a specific account to keep the data segregated
        $db->setSchema("acc_" . $sl->getAccount()->getId());

        return $db;
    }
}
