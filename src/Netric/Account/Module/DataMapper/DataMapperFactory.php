<?php

namespace Netric\Account\Module\DataMapper;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Db\Relational\RelationalDbFactory;
use Netric\Config\ConfigFactory;
use Netric\Entity\EntityLoaderFactory;

/**
 * Create a data mapper service for modules
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
        $db = $serviceLocator->get(RelationalDbFactory::class);
        $config = $serviceLocator->get(ConfigFactory::class);
        $account = $serviceLocator->getApplication()->getAccount();
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);

        return new ModuleRdbDataMapper($db, $config, $entityLoader);
    }
}
