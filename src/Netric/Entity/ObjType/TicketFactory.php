<?php

namespace Netric\Entity\ObjType;

use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Entity\EntityFactoryInterface;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityGroupings\GroupingLoaderFactory;

/**
 * Create a new ticket entity
 */
class TicketFactory implements EntityFactoryInterface
{
    /**
     * Entity creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @param EntityDefinition $def The definition of this type of object
     * @return EntityInterface TicketEntity
     */
    public static function create(ServiceLocatorInterface $serviceLocator, EntityDefinition $def)
    {
        $groupingLoader = $serviceLocator->get(GroupingLoaderFactory::class);
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        return new TicketEntity($def, $entityLoader, $groupingLoader);
    }
}
