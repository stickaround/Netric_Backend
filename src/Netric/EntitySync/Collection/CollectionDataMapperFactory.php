<?php

declare(strict_types=1);

namespace Netric\EntitySync\Collection;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Db\Relational\RelationalDbContainerFactory;
//use Netric\ServiceManager; I think no need to import this, comment out for now

/**
 * Create a Entity Sync Commit DataMapper service
 */
class CollectionDataMapperFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return CollectionDataMapperInterface
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $relationalDbCon = $serviceLocator->get(RelationalDbContainerFactory::class);

        return new CollectionRdbDataMapper($relationalDbCon);
    }
}
