<?php

/**
 * Factory used to create WorkFlowLegacy instance
 */

namespace Netric\WorkFlowLegacy;

use Netric\WorkFlowLegacy\Action\ActionFactory;
use Netric\ServiceManager;

/**
 * Create a WorkFlowLegacy instance
 *
 * @package Netric\FileSystem
 */
class WorkFlowLegacyFactory implements ServiceManager\AccountServiceFactoryInterface
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
        return new WorkFlowLegacy($actionFactory);
    }
}
