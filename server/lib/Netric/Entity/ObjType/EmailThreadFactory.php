<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Entity\ObjType;

use Netric\ServiceManager;
use Netric\Entity;

/**
 * Create a new email thread entity
 */
class EmailThreadFactory implements Entity\EntityFactoryInterface
{
    /**
     * Entity creation factory
     *
     * @param ServiceManager\ServiceLocatorInterface $sl ServiceLocator for injecting dependencies
     * @return new Entity\EntityInterface object
     */
    public static function create(ServiceManager\ServiceLocatorInterface $sl)
    {
        $def = $sl->get("EntityDefinitionLoader")->get("email_thread");
        $entityLoader = $sl->get("EntityLoader");
        $entityQueryIndex = $sl->get("EntityQuery_Index");
        return new EmailThreadEntity($def, $entityLoader, $entityQueryIndex);
    }
}
