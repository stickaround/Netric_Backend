<?php

namespace Netric\Db\Relational;

use Netric\ServiceManager\AccountServiceFactoryInterface;
use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\Config\ConfigFactory;

/**
 * Create a service that returns a handle to an account database
 */
class RelationalDbFactory implements AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return RelationalDbInterface
     */
    public function createService(AccountServiceManagerInterface $sl)
    {
        $config = $sl->get(ConfigFactory::class);

        $db = new PgsqlDb(
            $config->db["host"],
            $sl->getAccount()->getDatabaseName(),
            $config->db["user"],
            $config->db["password"]
        );

        // Set the schema to a specific account to keep the data segregated
        $db->setNamespace('acc_' . $sl->getAccount()->getId());

        return $db;
    }
}
