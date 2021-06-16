<?php
namespace Netric\Entity\BrowserView;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Config\ConfigFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\Settings\SettingsFactory;
use Netric\Db\Relational\RelationalDbFactory;
use Netric\EntityGroupings\GroupingLoaderFactory;

/**
 * Create a new BrowserView service for getting and saving forms
 *
 * @package Netric\FileSystem
 */
class BrowserViewServiceFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return FileSystem
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $config = $serviceLocator->get(ConfigFactory::class);
        $defLoader = $serviceLocator->get(EntityDefinitionLoaderFactory::class);
        $settings = $serviceLocator->get(SettingsFactory::class);
        $rdb = $serviceLocator->get(RelationalDbFactory::class);
        $groupingLoader = $serviceLocator->get(GroupingLoaderFactory::class);
        return new BrowserViewService($rdb, $config, $defLoader, $settings, $groupingLoader);
    }
}
