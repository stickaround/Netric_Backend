<?php
namespace Netric\Entity\BrowserView;

use Netric\ServiceManager;
use Netric\Config\ConfigFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\Settings\SettingsFactory;
use Netric\Db\Relational\RelationalDbFactory;

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
        $config = $sl->get(ConfigFactory::class);
        $defLoader = $sl->get(EntityDefinitionLoaderFactory::class);
        $settings = $sl->get(SettingsFactory::class);
        $rdb = $sl->get(RelationalDbFactory::class);
        return new BrowserViewService($rdb, $config, $defLoader, $settings);
    }
}
