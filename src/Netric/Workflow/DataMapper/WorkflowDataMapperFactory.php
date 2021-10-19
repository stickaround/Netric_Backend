<?php

declare(strict_types=1);

namespace Netric\Workflow\DataMapper;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;

/**
 * service factory to construct the workflow datamapper
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
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $entityIndex = $serviceLocator->get(IndexFactory::class);
        return new WorkflowEntityDataMapper($entityLoader, $entityIndex);
    }
}
