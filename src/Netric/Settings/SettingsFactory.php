<?php
namespace Netric\Settings;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Db\Relational\RelationalDbFactory;
use Netric\Cache\CacheFactory;

/**
 * Create a new settings service
 *
 * @package Netric\FileSystem
 */
class SettingsFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return FileSystem
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $database = $serviceLocator->get(RelationalDbFactory::class);
        $cache = $serviceLocator->get(CacheFactory::class);
        return new Settings($database, $cache);
    }
}
