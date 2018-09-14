<?php
/**
 * Task entity type
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Entity\ObjType;

use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\ServiceManager;
use Netric\Entity;
use Netric\EntityDefinition\ObjectTypes;

/**
 * Create a new task entity
 */
class TaskFactory implements Entity\EntityFactoryInterface
{
    /**
     * Entity creation factory
     *
     * @param \Netric\ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return new Entity\EntityInterface object
     */
    public static function create(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $def = $sl->get(EntityDefinitionLoaderFactory::class)->get(ObjectTypes::TASK);
        return new TaskEntity($def);
    }
}
