<?php

namespace Netric\Entity\ObjType;

use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Entity\EntityFactoryInterface;
use Netric\Entity\EntityInterface;
use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Entity\EntityLoaderFactory;

/**
 * Create a new payment profile entity
 */
class PaymentProfileFactory implements EntityFactoryInterface
{
    /**
     * Entity creation factory
     *
     * @param ServiceContainerInterface $serviceLocator ServiceLocator for injecting dependencies
     * @param EntityDefinition $def The definition of this type of object
     * @return EntityInterface PaymentProfileEntity
     */
    public static function create(ServiceContainerInterface $serviceLocator, EntityDefinition $def)
    {
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        return new PaymentProfileEntity($def, $entityLoader);
    }
}
