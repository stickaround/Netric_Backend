<?php
/**
 * Factory used to create WorkFlow instance
 */
namespace Netric\WorkFlow;

use Netric\WorkFlow\Action\ActionFactory;
use Netric\ServiceManager;

/**
 * Create a WorkFlow instance
 *
 * @package Netric\FileSystem
 */
class WorkFlowFactory implements ServiceManager\AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param \Netric\ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return FileSystem
     */
    public function createService(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $actionFactory = new ActionFactory($sl);
        return new WorkFlow($actionFactory);
    }
}
