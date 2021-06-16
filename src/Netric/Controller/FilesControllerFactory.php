<?php

namespace Netric\Controller;

use Netric\Mvc\ControllerFactoryInterface;
use Netric\Mvc\ControllerInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Account\AccountContainerFactory;
use Netric\Authentication\AuthenticationServiceFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\FileSystem\FileSystemFactory;
use Netric\FileSystem\ImageResizerFactory;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Netric\Permissions\DaclLoaderFactory;
use Netric\Log\LogFactory;

/**
 * Construct the FilesControllerFactory for interacting with email messages
 */
class FilesControllerFactory implements ControllerFactoryInterface
{
    /**
     * Construct a controller and return it
     *
     * @param ServiceContainerInterface $serviceLocator
     * @return ControllerInterface
     */
    public function get(ServiceContainerInterface $serviceLocator): ControllerInterface
    {
        $accountContainer = $serviceLocator->get(AccountContainerFactory::class);
        $authService = $serviceLocator->get(AuthenticationServiceFactory::class);
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $groupingLoader = $serviceLocator->get(GroupingLoaderFactory::class);
        $daclLoader = $serviceLocator->get(DaclLoaderFactory::class);
        $fileSystem = $serviceLocator->get(FileSystemFactory::class);
        $imageResizer = $serviceLocator->get(ImageResizerFactory::class);
        $log = $serviceLocator->get(LogFactory::class);

        return new FilesController(
            $accountContainer,
            $authService,
            $entityLoader,
            $groupingLoader,
            $daclLoader,
            $fileSystem,
            $imageResizer,
            $log
        );
    }
}
