<?php
/**
 * Folder entity type
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Entity\ObjType;

use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\Entity\EntityFactoryInterface;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityQuery\Index\IndexFactory;

/**
 * Create a new folder entity
 */
class FolderFactory implements EntityFactoryInterface
{
    /**
     * Entity creation factory
     *
     * @param AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return EntityInterface
     */
    public static function create(AccountServiceManagerInterface $sl)
    {
        $def = $sl->get(EntityDefinitionLoaderFactory::class)->get(ObjectTypes::FOLDER);
        $entityloader = $sl->get(EntityLoaderFactory::class);
        $entityIndex = $sl->get(IndexFactory::class);

        return new FolderEntity($def, $entityloader, $entityIndex);
    }
}
