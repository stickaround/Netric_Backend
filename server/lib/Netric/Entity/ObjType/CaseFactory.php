<?php
/**
 * Case entity type
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Entity\ObjType;

use Netric\ServiceManager;
use Netric\Entity;

/**
 * Create a new case entity
 */
class CaseFactory implements Entity\EntityFactoryInterface
{
    /**
     * Entity creation factory
     *
     * @param \Netric\ServiceManager\ServiceLocatorInterface $sl ServiceLocator for injecting dependencies
     * @return new Entity\EntityInterface object
     */
    public static function create(ServiceManager\ServiceLocatorInterface $sl)
    {
        $def = $sl->get("EntityDefinitionLoader")->get("case");
        return new CaseEntity($def);
    }
}
