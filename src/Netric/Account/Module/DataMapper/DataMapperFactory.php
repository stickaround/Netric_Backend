<?php

namespace Netric\Account\Module\DataMapper;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Db\Relational\RelationalDbFactory;
use Netric\Config\ConfigFactory;
use Netric\Entity\EntityLoaderFactory;

/**
 * Create a data mapper service for modules
 */
class DataMapperFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return DataMapperInterface
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $db = $serviceLocator->get(RelationalDbFactory::class);
        $config = $serviceLocator->get(ConfigFactory::class);
        $account = $serviceLocator->getApplication()->getAccount();
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);

        return new ModuleRdbDataMapper($db, $config, $entityLoader);
    }
}
