<?php
namespace Netric\FileSystem\FileStore;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Config\ConfigFactory;
use Netric\Entity\EntityLoaderFactory;

/**
 * Create a file system storage service that uses aereus object storage
 */
class ObjectStorageStoreFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return ObjectStorageStore
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
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
