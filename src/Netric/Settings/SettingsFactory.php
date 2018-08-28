<?php
namespace Netric\Settings;

use Netric\ServiceManager;
use Netric\Db\Relational\RelationalDbFactory;
use Netric\Cache\CacheFactory;

/**
 * Create a new settings service
 *
 * @package Netric\FileSystem
 */
class SettingsFactory implements ServiceManager\AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param \Netric\ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return FileSystem
     */
    public function createService(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $database = $sl->get(RelationalDbFactory::class);
        $cache = $sl->get(CacheFactory::class);
        $account = $sl->getAccount();
        return new Settings($database, $account, $cache);
    }
}
