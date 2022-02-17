<?php

/**
 * Folder entity type
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\Entity\ObjType;

use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Entity\EntityFactoryInterface;
use Netric\Entity\EntityInterface;
use Netric\EntityDefinition\EntityDefinition;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;

/**
 * Create a new folder entity
 */
class FolderFactory implements EntityFactoryInterface
{
    /**
     * Entity creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @param EntityDefinition $def The definition of this type of object
     * @return EntityInterface FolderEntity
     */
    public static function create(ServiceLocatorInterface $serviceLocator, EntityDefinition $def)
    {
        $entityloader = $serviceLocator->get(EntityLoaderFactory::class);
        $groupingLoader = $serviceLocator->get(GroupingLoaderFactory::class);
        $entityIndex = $serviceLocator->get(IndexFactory::class);

        return new FolderEntity($def, $entityloader, $groupingLoader, $entityIndex);
    }
}
