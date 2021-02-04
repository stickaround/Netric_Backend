<?php
/**
 * Factory used to initialize the netric filesystem filestore
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\FileSystem\FileStore;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Config\ConfigFactory;
use Netric\FileSystem\FileStore\MogileFileStoreFactory;
use Netric\FileSystem\FileStore\LocalFileStoreFactory;

/**
 * Create a file system storage service
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
        $store = $config->get('files')->get('store');

        if ($store === "mogile") {
            return $serviceLocator->get(MogileFileStoreFactory::class);
        } else {
            return $serviceLocator->get(LocalFileStoreFactory::class);
        }
    }
}
