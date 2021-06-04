<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Mail;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\FileSystem\FileSystemFactory;
use Netric\Log\LogFactory;

/**
 * Create a service for delivering mail
 */
class DeliveryServiceFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return DeliveryService
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $groupingsLoader = $serviceLocator->get(GroupingLoaderFactory::class);
        $log = $serviceLocator->get(LogFactory::class);
        $index = $serviceLocator->get(IndexFactory::class);
        $fileSystem = $serviceLocator->get(FileSystemFactory::class);

        return new DeliveryService(
            $log,
            $entityLoader,
            $groupingsLoader,
            $index,
            $fileSystem
        );
    }
}
