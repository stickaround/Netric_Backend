<?php

/**
 * Factory used to create WorkFlowLegacy instance
 */

namespace Netric\WorkFlowLegacy;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\WorkFlowLegacy\Action\ActionFactory;

/**
 * Create a WorkFlowLegacy instance
 *
 * @package Netric\FileSystem
 */
class WorkFlowLegacyFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return FileSystem
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $actionFactory = new ActionFactory($serviceLocator);
        return new WorkFlowLegacy($actionFactory);
    }
}
