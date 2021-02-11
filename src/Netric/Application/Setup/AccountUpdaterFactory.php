<?php
namespace Netric\Application\Setup;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Application\Setup\AccountUpdater;
use Netric\Settings\SettingsFactory;
use Netric\Log\LogFactory;

/**
 * Create a new AccountUpdater service
 */
class AccountUpdaterFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return AccountUpdater
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $settings = $serviceLocator->get(SettingsFactory::class);
        $log = $serviceLocator->get(LogFactory::class);
        return new AccountUpdater($settings, $log);
    }
}
