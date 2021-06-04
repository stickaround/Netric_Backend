<?php

/**
 * Factory used to create WorkFlowLegacy instance
 */

namespace Netric\WorkFlowLegacy;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\WorkFlowLegacy\Action\ActionFactory;

/**
 * Create a WorkFlowLegacy instance
 *
 * @package Netric\FileSystem
 */
class WorkFlowLegacyFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return FileSystem
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $actionFactory = new ActionFactory($serviceLocator);
        return new WorkFlowLegacy($actionFactory);
    }
}
