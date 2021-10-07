<?php

/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */

namespace Netric\Mail;

use Netric\Account\AccountContainerFactory;
use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\FileSystem\FileSystemFactory;
use Netric\Log\LogFactory;

/**
 * Create a service for delivering mail
 */
class DeliveryServiceFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return DeliveryService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $groupingsLoader = $serviceLocator->get(GroupingLoaderFactory::class);
        $log = $serviceLocator->get(LogFactory::class);
        $index = $serviceLocator->get(IndexFactory::class);
        $fileSystem = $serviceLocator->get(FileSystemFactory::class);
        $mailSystem = $serviceLocator->get(MailSystemFactory::class);
        $accountContainer = $serviceLocator->get(AccountContainerFactory::class);

        return new DeliveryService(
            $mailSystem,
            $log,
            $entityLoader,
            $groupingsLoader,
            $index,
            $fileSystem,
            $accountContainer
        );
    }
}
