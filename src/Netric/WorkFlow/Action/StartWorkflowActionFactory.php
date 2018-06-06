<?php
namespace Netric\WorkFlow\Action;

use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\WorkFlow\WorkFlowManagerFactory;

/**
 * Factory to create a new StartWorkflowAction
 */
class StartWorkflowActionFactory
{
    /**
     * Construct new action
     *
     * @param AccountServiceManagerInterface $serviceLocator For loading dependencies
     * @return ActionInterface
     */
    public static function create(AccountServiceManagerInterface $serviceLocator)
    {
        // Return a new TestAction
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $actionFactory = new ActionFactory($serviceLocator);
        $workflowManager = $serviceLocator->get(WorkFlowManagerFactory::class);
        return new StartWorkflowAction($entityLoader, $actionFactory, $workflowManager);
    }
}
