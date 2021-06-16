<?php

declare(strict_types=1);

namespace Netric\Account\InitData\Sets;

use Netric\FileSystem\FileSystemFactory;
use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Return data intializer
 */
class RootFolderInitDataFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return InitDataInterface[]
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $fileSystem = $serviceLocator->get(FileSystemFactory::class);
        return new RootFolderInitData($fileSystem);
    }
}
