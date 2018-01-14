<?php

/**
 * Factory used to start the WorkFLow internal service
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\WorkFlow;

use Netric\ServiceManager;
use Netric\Log\LogFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\WorkFlow\DataMapper\DataMapperFactory;

/**
 * Create a WorkFlow Management service
 *
 * @package Netric\FileSystem
 */
class WorkFlowManagerFactory implements ServiceManager\AccountServiceLocatorInterface
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
        $dataMapper = $sl->get(DataMapperFactory::class);
        $entityIndex = $sl->get(IndexFactory::class);
        $log = $sl->get(LogFactory::class);

        return new WorkFlowManager($dataMapper, $entityIndex, $log);
    }
}
