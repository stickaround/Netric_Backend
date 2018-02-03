<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Entity\ObjType;

use Netric\FileSystem\FileSystemFactory;
use Netric\ServiceManager;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityFactoryInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;

/**
 * Create a new email entity
 */
class EmailMessageFactory implements EntityFactoryInterface
{
    /**
     * Entity creation factory
     *
     * @param ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return EntityInterface
     */
    public static function create(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $def = $sl->get(EntityDefinitionLoaderFactory::class)->get("email_message");
        $entityLoader = $sl->get(EntityLoaderFactory::class);
        $entityQueryIndex = $sl->get(IndexFactory::class);
        $fileSystem = $sl->get(FileSystemFactory::class);
        return new EmailMessageEntity($def, $entityLoader, $entityQueryIndex, $fileSystem);
    }
}
