<?php
namespace Netric\Entity;

use Netric\ServiceManager;
use Netric\EntityLoaderFactory;
use Netric\EntityGroupings\LoaderFactory;

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
        $entityLoader = $sl->get(EntityLoaderFactory::class);
        $groupingsLoader = $sl->get(LoaderFactory::class);
        $currentUser = $sl->getAccount()->getUser();
        $log = $sl->getAccount()->getApplication()->getLog();

        return new ActivityLog($log, $entityLoader, $groupingsLoader, $currentUser);
    }
}
