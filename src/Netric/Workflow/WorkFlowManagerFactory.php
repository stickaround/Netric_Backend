<?php

/**
 * Factory used to start the WorkFLow internal service
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\WorkFlowLegacy;

use Netric\ServiceManager;
use Netric\Log\LogFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\WorkFlowLegacy\DataMapper\WorkflowDataMapperFactory;

/**
 * Create a WorkFlowLegacy Management service
 *
 * @package Netric\FileSystem
 */
class WorkFlowLegacyManagerFactory implements ServiceManager\AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param \Netric\ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return FileSystem
     */
    public function createService(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $user = $sl->getAccount()->getUser();
        $dataMapper = $sl->get(WorkflowDataMapperFactory::class);
        $entityIndex = $sl->get(IndexFactory::class);
        $log = $sl->get(LogFactory::class);

        return new WorkFlowLegacyManager($dataMapper, $entityIndex, $log);
    }
}
