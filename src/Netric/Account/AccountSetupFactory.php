<?php
namespace Netric\Account;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Application\DataMapperFactory;

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
        return new AccountSetup($dataMapper);
    }
}
