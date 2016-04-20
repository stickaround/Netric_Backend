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
        $entityLoader = $sl->get("EntityLoader");
        $entityIndex = $sl->get("EntityQuery_Index");

        return new EntityAggregator($entityLoader, $entityIndex);
    }
}
