<?php
namespace Netric\Settings;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Db\Relational\RelationalDbFactory;
use Netric\Cache\CacheFactory;

/**
 * Create a new settings service
 *
 * @package Netric\FileSystem
 */
class SettingsFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return FileSystem
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $database = $serviceLocator->get(RelationalDbFactory::class);
        $cache = $serviceLocator->get(CacheFactory::class);
        return new Settings($database, $cache);
    }
}
