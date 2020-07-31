<?php

namespace Netric\Account\Module\DataMapper;

use Netric\ServiceManager\AccountServiceFactoryInterface;
use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\Db\Relational\RelationalDbFactory;
use Netric\Config\ConfigFactory;

/**
 * Create a data mapper service for modules
 */
class DataMapperFactory implements AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param AccountServiceManagerInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return DataMapperInterface
     */
    public function createService(AccountServiceManagerInterface $serviceLocator)
    {
        $db = $serviceLocator->get(RelationalDbFactory::class);
        $config = $serviceLocator->get(ConfigFactory::class);
        $account = $serviceLocator->getAccount();

        return new ModuleRdbDataMapper($db, $config, $account);
    }
}
