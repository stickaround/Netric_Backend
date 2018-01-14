<?php
namespace Netric\Entity\Notifier;

use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\ServiceManager\AccountServiceLocatorInterface;
use Netric\EntityLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;

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
        $entityLoader = $serviceManager->get(EntityLoaderFactory::class);
        $entityIndex = $serviceManager->get(IndexFactory::class);
        $currentUser = $serviceManager->getAccount()->getUser();
        return new Notifier($currentUser, $entityLoader, $entityIndex);
    }
}
