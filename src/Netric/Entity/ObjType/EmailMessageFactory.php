<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Entity\ObjType;

use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Entity\EntityFactoryInterface;
use Netric\FileSystem\FileSystemFactory;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Account\AccountContainerFactory;

/**
 * Create a new email entity
 */
class EmailMessageFactory implements EntityFactoryInterface
{
    /**
     * Entity creation factory
     *
     * @param ServiceContainerInterface $serviceLocator ServiceLocator for injecting dependencies
     * @param EntityDefinition $def The definition of this type of object
     * @return EntityInterface EmailMessageEntity
     */
    public static function create(ServiceContainerInterface $serviceLocator, EntityDefinition $def)
    {
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $entityQueryIndex = $serviceLocator->get(IndexFactory::class);
        $fileSystem = $serviceLocator->get(FileSystemFactory::class);
        $accountContainer = $serviceLocator->get(AccountContainerFactory::class);
        return new EmailMessageEntity($def, $entityLoader, $entityQueryIndex, $fileSystem, $accountContainer);
    }
}
