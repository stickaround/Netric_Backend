<?php
namespace Netric\FileSystem\FileStore;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Config\ConfigFactory;
use Netric\Entity\EntityLoaderFactory;

/**
 * Create a file system storage service that uses aereus object storage
 */
class ObjectStorageStoreFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return ObjectStorageStore
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);

        $config = $serviceLocator->get(ConfigFactory::class);
        $tmpPath = $config->data_path . "/" . "tmp";

        return new ObjectStorageStore(
            $entityLoader,
            $tmpPath,
            $config->files->osServer,
            $config->files->osAccount,
            $config->files->osSecret
        );
    }
}
