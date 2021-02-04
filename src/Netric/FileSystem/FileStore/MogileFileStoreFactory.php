<?php
namespace Netric\FileSystem\FileStore;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use MogileFs;
use Netric\Config\ConfigFactory;
use Netric\Entity\EntityLoaderFactory;

/**
 * Create a file system storage service that uses aereus network storage
 */
class MogileFileStoreFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return MogileFileStore
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);

        $config = $serviceLocator->get(ConfigFactory::class);
        $tmpPath = $config->data_path . "/" . "tmp";

        return new MogileFileStore(
            $entityLoader,
            $tmpPath,
            $config->files->server,
            $config->files->account,
            $config->files->port
        );
    }
}
