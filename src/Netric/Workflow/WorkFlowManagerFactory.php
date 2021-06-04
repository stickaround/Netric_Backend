<?php

/**
 * Factory used to start the WorkFLow internal service
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\WorkFlowLegacy;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Log\LogFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\WorkFlowLegacy\DataMapper\WorkflowDataMapperFactory;

/**
 * Create a WorkFlowLegacy Management service
 *
 * @package Netric\FileSystem
 */
class WorkFlowLegacyManagerFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return FileSystem
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $dataMapper = $serviceLocator->get(WorkflowDataMapperFactory::class);
        $entityIndex = $serviceLocator->get(IndexFactory::class);
        $log = $serviceLocator->get(LogFactory::class);

        return new WorkFlowLegacyManager($dataMapper, $entityIndex, $log);
    }
}
