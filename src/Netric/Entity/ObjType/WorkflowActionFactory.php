<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Entity\ObjType;

use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\ServiceManager;
use Netric\Entity;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Entity\EntityLoaderFactory;

/**
 * Create a new WorkflowAction entity
 */
class WorkflowActionFactory implements Entity\EntityFactoryInterface
{
    /**
     * Entity creation factory
     *
     * @param ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return new Entity\EntityInterface object
     */
    public static function create(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $def = $sl->get(EntityDefinitionLoaderFactory::class)->get(ObjectTypes::WORKFLOW_ACTION, $sl->getAccount()->getAccountId());
        $entityLoader = $sl->get(EntityLoaderFactory::class);
        return new WorkflowActionEntity($def, $entityLoader);
    }
}
