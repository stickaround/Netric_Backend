<?php
namespace Netric\Account;

use Netric\Account\InitData\InitDataSetsFactory;
use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Application\DataMapperFactory;

/**
 * Create a new AccountSetup service
 */
class AccountSetupFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return DataMapperInterface
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $dataMapper = $serviceLocator->get(DataMapperFactory::class);
        $dataImporters = $serviceLocator->get(InitDataSetsFactory::class);
        return new AccountSetup($dataMapper, $dataImporters);
    }
}
