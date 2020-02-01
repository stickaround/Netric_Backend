<?php
namespace Netric\Entity;

use Netric\ServiceManager;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityGroupings\GroupingLoaderFactory;

/**
 * Factory for constructing an activity log service
 */
class ActivityLogFactory implements ServiceManager\AccountServiceFactoryInterface
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
        $groupingsLoader = $sl->get(GroupingLoaderFactory::class);
        $currentUser = $sl->getAccount()->getUser();
        $log = $sl->getAccount()->getApplication()->getLog();

        return new ActivityLog($log, $entityLoader, $groupingsLoader, $currentUser);
    }
}
