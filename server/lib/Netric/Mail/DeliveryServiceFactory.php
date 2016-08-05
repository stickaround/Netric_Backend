<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Mail;

use Netric\EntitySync\Collection\CollectionFactory;
use Netric\ServiceManager;

/**
 * Create a service for delivering mail
 */
class DeliveryServiceFactory implements ServiceManager\AccountServiceLocatorInterface
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
        $collectionFactory = new CollectionFactory($sl);
        $entitySyncServer = $sl->get("Netric/EntitySync/EntitySync");
        $entityLoader = $sl->get("EntityLoader");
        $groupingsLoader = $sl->get("Netric/EntityGroupings/Loader");
        $log = $sl->get("Log");
        $index = $sl->get("EntityQuery_Index");

        return new DeliveryService(
            $log,
            $entitySyncServer,
            $collectionFactory,
            $entityLoader,
            $groupingsLoader,
            $index
        );
    }
}
