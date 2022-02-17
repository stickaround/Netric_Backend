<?php

/**
 * Project entity type
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
use Netric\EntityQuery\Index\IndexFactory;
use Netric\EntityGroupings\GroupingLoaderFactory;

/**
 * Create a new project entity
 */
class ProjectFactory implements EntityFactoryInterface
{
    /**
     * Entity creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @param EntityDefinition $def The definition of this type of object
     * @return EntityInterface ProjectEntity
     */
    public static function create(ServiceLocatorInterface $serviceLocator, EntityDefinition $def)
    {
        $entityIndex = $serviceLocator->get(IndexFactory::class);
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $groupingLoader = $serviceLocator->get(GroupingLoaderFactory::class);
        return new ProjectEntity($def, $entityLoader, $groupingLoader, $entityIndex);
    }
}
