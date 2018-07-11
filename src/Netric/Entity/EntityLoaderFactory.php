<?php
namespace Netric\Entity;

use Netric\Entity\EntityFactoryFactory;
use Netric\Cache\CacheFactory;
use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\ServiceManager\AccountServiceFactoryInterface;
use Netric\Entity\DataMapper\DataMapperFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;

/**
 * Create a Entity Loader service
 */
class EntityLoaderFactory implements AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return EntityLoader
     */
    public function createService(AccountServiceManagerInterface $sl)
    {
        $dm = $sl->get(DataMapperFactory::class);
        $definitionLoader = $sl->get(EntityDefinitionLoaderFactory::class);
        $entityFactory = $dm->getAccount()->getServiceManager()->get(EntityFactoryFactory::class);
        $cache = $dm->getAccount()->getServiceManager()->get(CacheFactory::class);

        return new EntityLoader($dm, $definitionLoader, $entityFactory, $cache);
    }
}
