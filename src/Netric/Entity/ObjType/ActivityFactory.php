<?php
namespace Netric\Entity\ObjType;

use Netric\ServiceManager;
use Netric\Entity;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;

/**
 * Create a new activity entity
 */
class ActivityFactory implements Entity\EntityFactoryInterface
{
    /**
     * Entity creation factory
     *
     * @param \Netric\ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return new Entity\EntityInterface object
     */
    public static function create(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $def = $sl->get(EntityDefinitionLoaderFactory::class)->get("activity");
        return new ActivityEntity($def);
    }
}
