<?php
/**
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Entity\Members;

use Netric\ServiceManager\ServiceFactoryInterface;

/**
 * Create a new Notifier service
 */
class MembersFactory implements ServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceManager ServiceLocator for injecting dependencies
     * @return Members
     */
    public function createService(ServiceLocatorInterface $serviceManager)
    {
        $notifier = $serviceManager->get("Netric/Entity/Notifier/Notifier");
        return new Members($notifier);
    }
}
