<?php
namespace Netric\Entity;

use Netric\ServiceManager;
use Netric\EntityLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;

/**
 * Create a new EntityAggregator service for updating aggregates
 *
 * @package Netric\FileSystem
 */
class EntityAggregatorFactory implements ServiceManager\AccountServiceLocatorInterface
{
    /**
     * Service creation factory
     *
     * @param \Netric\ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return FileSystem
     */
    public function createService(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $entityLoader = $sl->get(EntityLoaderFactory::class);
        $entityIndex = $sl->get(IndexFactory::class);

        return new EntityAggregator($entityLoader, $entityIndex);
    }
}
