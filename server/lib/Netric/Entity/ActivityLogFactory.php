<?php
/**
 * Service factory for the ActivityLog
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Entity;

use Netric\ServiceManager;

/**
 * Factory for constructing an activity log service
 */
class ActivityLogFactory implements ServiceManager\ServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param \Netric\ServiceManager\ServiceLocatorInterface $sl ServiceLocator for injecting dependencies
     * @return FileSystem
     */
    public function createService(ServiceManager\ServiceLocatorInterface $sl)
    {
        $entityLoader = $sl->get("EntityLoader");
        $groupingsLoader = $sl->get("EntityGroupings_Loader");
        $currentUser = $sl->getAccount()->getUser();

        return new ActivityLog($entityLoader, $groupingsLoader, $currentUser);
    }
}
