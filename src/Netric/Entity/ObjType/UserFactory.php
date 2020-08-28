<?php

/**
 * Default entity type
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\Entity\ObjType;

use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\ServiceManager;
use Netric\Entity;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityGroupings\GroupingLoaderFactory;

/**
 * Create a new default object type entity
 */
class UserFactory implements Entity\EntityFactoryInterface
{
    /**
     * Entity creation factory
     *
     * @param \Netric\ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return new Entity\EntityInterface object
     */
    public static function create(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $def = $sl->get(EntityDefinitionLoaderFactory::class)->get(ObjectTypes::USER, $sl->getAccount()->getAccountId());
        $entityLoader = $sl->get(EntityLoaderFactory::class);
        $groupingLoader = $sl->get(GroupingLoaderFactory::class);
        return new UserEntity($def, $entityLoader, $groupingLoader);
    }
}
