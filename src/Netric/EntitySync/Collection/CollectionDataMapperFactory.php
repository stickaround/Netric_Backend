<?php

declare(strict_types=1);

namespace Netric\EntitySync\Collection;

use Netric\Db\Relational\RelationalDbContainerFactory;
use Netric\ServiceManager\AccountServiceFactoryInterface;
use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\ServiceManager;

/**
 * Create a Entity Sync Commit DataMapper service
 */
class CollectionDataMapperFactory implements AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param AccountServiceManagerInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return CollectionDataMapperInterface
     */
    public function createService(AccountServiceManagerInterface $serviceLocator)
    {
        $relationalDbCon = $serviceLocator->get(RelationalDbContainerFactory::class);

        return new CollectionRdbDataMapper($relationalDbCon);
    }
}
