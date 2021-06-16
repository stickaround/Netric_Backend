<?php

/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2017 Aereus
 */

namespace Netric\Entity;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Account\AccountContainerFactory;
use Netric\FileSystem\FileSystemFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\Log\LogFactory;

/**
 * Create a service for delivering mail
 */
class EntityMaintainerServiceFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return EntityMaintainerService
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $log = $serviceLocator->get(LogFactory::class);
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $entityIndex = $serviceLocator->get(IndexFactory::class);
        $entityDefinitionLoader = $serviceLocator->get(EntityDefinitionLoaderFactory::class);
        $fileSystem = $serviceLocator->get(FileSystemFactory::class);
        $accountContainer = $serviceLocator->get(AccountContainerFactory::class);
        return new EntityMaintainerService(
            $log,
            $entityLoader,
            $entityDefinitionLoader,
            $entityIndex,
            $fileSystem,
            $accountContainer
        );
    }
}
