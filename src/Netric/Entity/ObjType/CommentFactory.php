<?php
/**
 * Comment entity type
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Entity\ObjType;

use Netric\ServiceManager;
use Netric\Entity;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\EntityDefinition\ObjectTypes;

/**
 * Create a new comment entity
 */
class CommentFactory implements Entity\EntityFactoryInterface
{
    /**
     * Entity creation factory
     *
     * @param \Netric\ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return new Entity\EntityInterface object
     */
    public static function create(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $def = $sl->get(EntityDefinitionLoaderFactory::class)->get(ObjectTypes::COMMENT);
        return new CommentEntity($def);
    }
}
