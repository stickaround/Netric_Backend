<?php

namespace Netric\FileSystem;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\FileSystem\FileStore\FileStoreFactory;
use Netric\Entity\DataMapper\EntityDataMapperFactory;
use Netric\EntityQuery\Index\IndexFactory;

/**
 * Create a file system service
 */
class FileSystemFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return FileSystem
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $fileStore = $serviceLocator->get(FileStoreFactory::class);
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $entityIndex = $serviceLocator->get(IndexFactory::class);

        return new FileSystem($fileStore, $entityLoader, $entityIndex);
    }
}
