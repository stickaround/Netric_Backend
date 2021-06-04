<?php

declare(strict_types=1);

namespace Netric\Account\InitData\Sets;

use Netric\FileSystem\FileSystemFactory;
use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;

/**
 * Return data intializer
 */
class RootFolderInitDataFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return InitDataInterface[]
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $fileSystem = $serviceLocator->get(FileSystemFactory::class);
        return new RootFolderInitData($fileSystem);
    }
}
