<?php
namespace Netric\Entity\BrowserView;

use Netric\ServiceManager;
use Netric\Config\ConfigFactory;
use Netric\Db\DbFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\Settings\SettingsFactory;

/**
 * Create a new BrowserView service for getting and saving forms
 *
 * @package Netric\FileSystem
 */
class BrowserViewServiceFactory implements ServiceManager\AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param \Netric\ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return FileSystem
     */
    public function createService(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $dbh = $sl->get(DbFactory::class);
        $config = $sl->get(ConfigFactory::class);
        $defLoader = $sl->get(EntityDefinitionLoaderFactory::class);
        $settings = $sl->get(SettingsFactory::class);
        return new BrowserViewService($dbh, $config, $defLoader, $settings);
    }
}
