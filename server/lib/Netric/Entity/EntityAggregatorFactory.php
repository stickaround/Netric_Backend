<?php
/**
 * Service factory for the EntityAggregator
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Entity;

use Netric\ServiceManager;

/**
 * Create a new EntityAggregator service for updating aggregates
 *
 * @package Netric\FileSystem
 */
class EntityAggregatorFactory implements ServiceManager\ServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param \Netric\ServiceManager\ServiceLocatorInterface $sl ServiceLocator for injecting dependencies
     * @return FileSystem
     */
    public function createService(ServiceManager\ServiceLocatorInterface $sl)
    {
        $entityLoader = $sl->get("EntityLoader");
        $entityIndex = $sl->get("EntityQuery_Index");

        return new EntityAggregator($entityLoader, $entityIndex);
    }
}
