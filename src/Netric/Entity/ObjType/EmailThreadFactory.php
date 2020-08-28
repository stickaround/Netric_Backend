<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Entity\ObjType;

use Netric\Entity\EntityInterface;
use Netric\Entity\EntityFactoryInterface;
use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\EntityDefinition\ObjectTypes;

/**
 * Create a new email thread entity
 */
class EmailThreadFactory implements EntityFactoryInterface
{
    /**
     * Entity creation factory
     *
     * @param AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return EntityInterface
     */
    public static function create(AccountServiceManagerInterface $sl)
    {
        $def = $sl->get(EntityDefinitionLoaderFactory::class)->get(ObjectTypes::EMAIL_THREAD, $sl->getAccount()->getAccountId());
        $entityLoader = $sl->get(EntityLoaderFactory::class);
        $entityQueryIndex = $sl->get(IndexFactory::class);
        return new EmailThreadEntity($def, $entityLoader, $entityQueryIndex);
    }
}
