<?php

/**
 * Email Account entity type
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */

namespace Netric\Entity\ObjType;

use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Entity\EntityFactoryInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityGroupings\GroupingLoaderFactory;

/**
 * Create a new activity entity
 */
class EmailAccountFactory implements EntityFactoryInterface
{
    /**
     * Entity creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @param EntityDefinition $def The definition of this type of object
     * @return EntityInterface EmailAccountEntity
     */
    public static function create(ServiceLocatorInterface $serviceLocator, EntityDefinition $def)
    {
        return new EmailAccountEntity(
            $def,
            $serviceLocator->get(EntityLoaderFactory::class),
            $serviceLocator->get(GroupingLoaderFactory::class)
        );
    }
}
