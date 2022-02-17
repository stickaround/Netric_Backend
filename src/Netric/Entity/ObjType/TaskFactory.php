<?php

/**
 * Task entity type
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */

namespace Netric\Entity\ObjType;

use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Entity\EntityFactoryInterface;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityGroupings\GroupingLoaderFactory;

/**
 * Create a new task entity
 */
class TaskFactory implements EntityFactoryInterface
{
    /**
     * Entity creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @param EntityDefinition $def The definition of this type of object
     * @return EntityInterface TaskEntity
     */

    public static function create(ServiceLocatorInterface $serviceLocator, EntityDefinition $def)
    {
        $groupingLoader = $serviceLocator->get(GroupingLoaderFactory::class);
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        return new TaskEntity(
            $def,
            $entityLoader,
            $groupingLoader
        );
    }
}
