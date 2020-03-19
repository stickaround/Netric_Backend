<?php
/**
 * Dashboard entity type
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2018 Aereus
 */
namespace Netric\Entity\ObjType;

use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\ServiceManager;
use Netric\Entity;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Entity\EntityLoaderFactory;

/**
 * Create a new dashboard entity
 */
class DashboardFactory implements Entity\EntityFactoryInterface
{
    /**
     * Entity creation factory
     *
     * @param \Netric\ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return new Entity\EntityInterface object
     */
    public static function create(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $def = $sl->get(EntityDefinitionLoaderFactory::class)->get(ObjectTypes::DASHBOARD);
        $entityLoader = $sl->get(EntityLoaderFactory::class);
        return new DashboardEntity($def, $entityLoader);
    }
}
