<?php

namespace Netric\WorkFlowLegacy\DataMapper;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\WorkFlowLegacy\Action\ActionFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\Db\Relational\RelationalDbFactory;

/**
 * Base DataMapper class
 */
class WorkflowDataMapperFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return WorkflowDataMapperInterface
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $actionFactory = new ActionFactory($serviceLocator);
        $database = $serviceLocator->get(RelationalDbFactory::class);
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $entityIndex = $serviceLocator->get(IndexFactory::class);
        return new WorkFlowLegacyRdbDataMapper($database, $entityLoader, $entityIndex, $actionFactory);
    }
}
