<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Mail;

use Netric\ServiceManager;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\FileSystem\FileSystemFactory;
use Netric\Log\LogFactory;

/**
 * Create a service for delivering mail
 */
class DeliveryServiceFactory implements ServiceManager\AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return DeliveryService
     */
    public function createService(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $user = $sl->getAccount()->getUser();
        $entityLoader = $sl->get(EntityLoaderFactory::class);
        $groupingsLoader = $sl->get(GroupingLoaderFactory::class);
        $log = $sl->get(LogFactory::class);
        $index = $sl->get(IndexFactory::class);
        $fileSystem = $sl->get(FileSystemFactory::class);

        return new DeliveryService(
            $log,
            $entityLoader,
            $groupingsLoader,
            $index,
            $fileSystem
        );
    }
}
