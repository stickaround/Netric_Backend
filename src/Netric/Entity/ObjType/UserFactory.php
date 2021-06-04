<?php

/**
 * Default entity type
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\Entity\ObjType;

use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Entity\EntityFactoryInterface;
use Netric\Entity\EntityInterface;
use Netric\EntityDefinition\EntityDefinition;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Netric\Account\AccountContainerFactory;

/**
 * Create a new default object type entity
 */
class UserFactory implements EntityFactoryInterface
{
    /**
     * Entity creation factory
     *
     * @param ServiceContainerInterface $serviceLocator ServiceLocator for injecting dependencies
     * @param EntityDefinition $def The definition of this type of object
     * @return EntityInterface UserEntity
     */
    public static function create(ServiceContainerInterface $serviceLocator, EntityDefinition $def)
    {
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $groupingLoader = $serviceLocator->get(GroupingLoaderFactory::class);
        $accountContainer = $serviceLocator->get(AccountContainerFactory::class);
        return new UserEntity($def, $entityLoader, $groupingLoader, $accountContainer);
    }
}
