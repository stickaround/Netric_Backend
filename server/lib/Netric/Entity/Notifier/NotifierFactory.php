<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Entity\Notifier;

use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\ServiceManager\ServiceFactoryInterface;

/**
 * Create a new Notifier service
 */
class NotifierFactory implements ServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceManager ServiceLocator for injecting dependencies
     * @return Notifier
     */
    public function createService(ServiceLocatorInterface $serviceManager)
    {
        $entityLoader = $serviceManager->get("EntityLoader");
        $entityIndex = $serviceManager->get("EntityQuery_Index");
        $currentUser = $serviceManager->getAccount()->getUser();
        return new Notifier($currentUser, $entityLoader, $entityIndex);
    }
}
