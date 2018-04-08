<?php
namespace Netric\Entity\ObjType;

use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\Entity\EntityFactoryInterface;
use Netric\Entity\EntityInterface;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;

/**
 * Create a new payment profile entity
 */
class PaymentProfileFactory implements EntityFactoryInterface
{
    /**
     * Entity creation factory
     *
     * @param AccountServiceManagerInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return EntityInterface PaymentProfileEntity
     */
    public static function create(AccountServiceManagerInterface $serviceLocator)
    {
        $def = $serviceLocator->get(EntityDefinitionLoaderFactory::class)->get("notification");
        return new PaymentProfileEntity($def);
    }
}
