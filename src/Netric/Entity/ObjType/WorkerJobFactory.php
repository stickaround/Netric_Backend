<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Entity\ObjType;

use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\ServiceManager;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityFactoryInterface;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Entity\EntityLoaderFactory;

/**
 * Create a new WorkerJob entity
 */
class WorkerJobFactory implements EntityFactoryInterface
{
    /**
     * Entity creation factory
     *
     * @param ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return EntityInterface
     */
    public static function create(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $def = $sl->get(EntityDefinitionLoaderFactory::class)->get(ObjectTypes::WORKER_JOB, $sl->getAccount()->getAccountId());
        $entityLoader = $sl->get(EntityLoaderFactory::class);
        return new WorkerJobEntity($def, $entityLoader);
    }
}
