<?php

namespace Netric\WorkFlowLegacy\DataMapper;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\WorkFlowLegacy\Action\ActionFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\Db\Relational\RelationalDbFactory;

/**
 * Base DataMapper class
 */
class WorkflowDataMapperFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return WorkflowDataMapperInterface
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $actionFactory = new ActionFactory($serviceLocator);
        $database = $serviceLocator->get(RelationalDbFactory::class);
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $entityIndex = $serviceLocator->get(IndexFactory::class);
        return new WorkFlowLegacyRdbDataMapper($database, $entityLoader, $entityIndex, $actionFactory);
    }
}
