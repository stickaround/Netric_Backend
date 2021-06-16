<?php

/**
 * Factory used to start the WorkFLow internal service
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\WorkFlowLegacy;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Log\LogFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\WorkFlowLegacy\DataMapper\WorkflowDataMapperFactory;

/**
 * Create a WorkFlowLegacy Management service
 *
 * @package Netric\FileSystem
 */
class WorkFlowLegacyManagerFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return FileSystem
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $dataMapper = $serviceLocator->get(WorkflowDataMapperFactory::class);
        $entityIndex = $serviceLocator->get(IndexFactory::class);
        $log = $serviceLocator->get(LogFactory::class);

        return new WorkFlowLegacyManager($dataMapper, $entityIndex, $log);
    }
}
