<?php
/**
 * Customer entity type
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Entity\ObjType;

use Netric\ServiceManager;
use Netric\Entity;

/**
 * Create a new comment entity
 */
class CommentFactory implements Entity\EntityFactoryInterface
{
    /**
     * Entity creation factory
     *
     * @param \Netric\ServiceManager\ServiceLocatorInterface $sl ServiceLocator for injecting dependencies
     * @return new Entity\EntityInterface object
     */
    public static function create(ServiceManager\ServiceLocatorInterface $sl)
    {
        $def = $sl->get("EntityDefinitionLoader")->get("comment");
        return new Comment($def);
    }
}
