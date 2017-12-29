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
class ActivityLogFactory implements ServiceManager\AccountServiceLocatorInterface
{
    /**
     * Service creation factory
     *
     * @param \Netric\ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return ActivityLog
     */
    public function createService(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $entityLoader = $sl->get("EntityLoader");
        $groupingsLoader = $sl->get("EntityGroupings_Loader");
        $currentUser = $sl->getAccount()->getUser();
        $log = $sl->getAccount()->getApplication()->getLog();

        return new ActivityLog($log, $entityLoader, $groupingsLoader, $currentUser);
    }
}
