<?php

namespace Netric\FileSystem\FileStore;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Config\ConfigFactory;

/**
 * Factory used to initialize the netric filesystem filestore
 */
class FileStoreFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return FileStoreInterface
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $config = $serviceLocator->get(ConfigFactory::class);
        $storeFactory = $config->get('files')->get('store');
        return $serviceLocator->get($storeFactory);
    }
}
