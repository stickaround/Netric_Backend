<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Entity\Notifier;

use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\ServiceManager\AccountServiceLocatorInterface;

/**
 * Create a new Notifier service
 */
class NotifierFactory implements AccountServiceLocatorInterface
{
    /**
     * Service creation factory
     *
     * @param AccountServiceManagerInterface $serviceManager ServiceLocator for injecting dependencies
     * @return Notifier
     */
    public function createService(AccountServiceManagerInterface $serviceManager)
    {
        $entityLoader = $serviceManager->get("EntityLoader");
        $entityIndex = $serviceManager->get("EntityQuery_Index");
        $currentUser = $serviceManager->getAccount()->getUser();
        return new Notifier($currentUser, $entityLoader, $entityIndex);
    }
}
