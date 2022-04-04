<?php

/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2022 Aereus
 */

namespace Netric\Entity\ObjType;

use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Entity\EntityFactoryInterface;
use Netric\Entity\EntityInterface;
use Netric\EntityDefinition\EntityDefinition;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityGroupings\GroupingLoaderFactory;

/**
 * Create a new User Team entity
 */
class UserTeamFactory implements EntityFactoryInterface
{
    /**
     * Entity creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @param EntityDefinition $def The definition of this type of object
     * @return new EntityInterface UserTeamEntity
     */
    public static function create(ServiceLocatorInterface $serviceLocator, EntityDefinition $def)
    {
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $groupingLoader = $serviceLocator->get(GroupingLoaderFactory::class);
        return new UserTeamEntity($def, $entityLoader, $groupingLoader);
    }
}
