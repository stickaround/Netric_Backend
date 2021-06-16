<?php
namespace Netric\Entity;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;

/**
 * Create a new EntityAggregator service for updating aggregates
 *
 * @package Netric\FileSystem
 */
class EntityAggregatorFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return FileSystem
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $entityIndex = $serviceLocator->get(IndexFactory::class);

        return new EntityAggregator($entityLoader, $entityIndex);
    }
}
