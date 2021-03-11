<?php

namespace Netric\FileSystem\FileStore;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Config\ConfigFactory;

/**
 * Factory used to initialize the netric filesystem filestore
 */
class FileStoreFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return FileStoreInterface
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get(ConfigFactory::class);
        $storeFactory = $config->get('files')->get('store');
        return $serviceLocator->get($storeFactory);
    }
}
