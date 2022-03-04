<?php

namespace Netric\Account;

use Netric\Account\InitData\InitDataSetsFactory;
use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Application\DataMapperFactory;
use Netric\Config\ConfigFactory;
use Netric\Entity\EntityLoaderFactory;

/**
 * Create a new AccountSetup service
 */
class AccountSetupFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return DataMapperInterface
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $dataMapper = $serviceLocator->get(DataMapperFactory::class);
        $dataImporters = $serviceLocator->get(InitDataSetsFactory::class);
        $accountContainer = $serviceLocator->get(AccountContainerFactory::class);
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $config = $serviceLocator->get(ConfigFactory::class);
        return new AccountSetup(
            $dataMapper,
            $accountContainer,
            $dataImporters,
            $entityLoader,
            $config->main_account_id
        );
    }
}
