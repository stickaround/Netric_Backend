<?php
/**
 * Project entity type
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Entity\ObjType;

use Netric\Entity\EntityFactoryInterface;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\EntityDefinition\ObjectTypes;

/**
 * Create a new project entity
 */
class ProjectFactory implements EntityFactoryInterface
{
    /**
     * Entity creation factory
     *
     * @param AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return EntityInterface
     */
    public static function create(AccountServiceManagerInterface $sl)
    {
        $def = $sl->get(EntityDefinitionLoaderFactory::class)->get(ObjectTypes::PROJECT, $sl->getAccount()->getAccountId());
        $entityIndex = $sl->get(IndexFactory::class);
        $entityLoader = $sl->get(EntityLoaderFactory::class);
        return new ProjectEntity($def, $entityLoader, $entityIndex);
    }
}
