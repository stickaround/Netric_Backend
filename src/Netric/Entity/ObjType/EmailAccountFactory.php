<?php
/**
 * Email Account entity type
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Entity\ObjType;

use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Entity\EntityFactoryInterface;
use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Entity\EntityLoaderFactory;

/**
 * Create a new activity entity
 */
class EmailAccountFactory implements EntityFactoryInterface
{
    /**
     * Entity creation factory
     *
     * @param ServiceContainerInterface $serviceLocator ServiceLocator for injecting dependencies
     * @param EntityDefinition $def The definition of this type of object
     * @return EntityInterface EmailAccountEntity
     */
    public static function create(ServiceContainerInterface $serviceLocator, EntityDefinition $def)
    {
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        return new EmailAccountEntity($def, $entityLoader);
    }
}
