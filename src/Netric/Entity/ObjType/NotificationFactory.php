<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Entity\ObjType;

use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Entity\EntityFactoryInterface;
use Netric\Entity\EntityInterface;
use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Entity\EntityLoaderFactory;
use Netric\Account\AccountContainerFactory;

/**
 * Create a new notification entity
 */
class NotificationFactory implements EntityFactoryInterface
{
    /**
     * Entity creation factory
     *
     * @param ServiceContainerInterface $serviceLocator ServiceLocator for injecting dependencies
     * @param EntityDefinition $def The definition of this type of object
     * @return new EntityInterface NotificationEntity
     */
    public static function create(ServiceContainerInterface $serviceLocator, EntityDefinition $def)
    {
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $accountContainer = $serviceLocator->get(AccountContainerFactory::class);
        return new NotificationEntity($def, $entityLoader, $accountContainer);
    }
}
