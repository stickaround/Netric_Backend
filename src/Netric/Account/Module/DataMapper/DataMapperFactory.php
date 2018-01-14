<?php

/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Account\Module\DataMapper;

use Netric\ServiceManager;
use Netric\Db\Relational\RelationalDbFactory;
use Netric\Config\ConfigFactory;

/**
 * Create a data mapper service for modules
 */
class DataMapperFactory implements ServiceManager\AccountServiceLocatorInterface
{
    /**
     * Service creation factory
     *
     * @param \Netric\ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return DataMapperInterface
     */
    public function createService(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $db = $sl->get(RelationalDbFactory::class);
        $config = $sl->get(ConfigFactory::class);
        $currentUser = $sl->getAccount()->getUser();

        return new ModuleRdbDataMapper($db, $config, $currentUser);
    }
}
