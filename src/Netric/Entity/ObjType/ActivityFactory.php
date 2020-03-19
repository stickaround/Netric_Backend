<?php
namespace Netric\Entity\ObjType;

use Netric\ServiceManager;
use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\Entity;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Entity\EntityLoaderFactory;

/**
 * Create a new activity entity
 */
class ActivityFactory implements Entity\EntityFactoryInterface
{
    /**
     * Entity creation factory
     *
     * @param AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return new Entity\EntityInterface object
     */
    public static function create(AccountServiceManagerInterface $sl)
    {
        $def = $sl->get(EntityDefinitionLoaderFactory::class)->get(ObjectTypes::ACTIVITY);
        $entityLoader = $sl->get(EntityLoaderFactory::class);
        return new ActivityEntity($def, $entityLoader);
    }
}
