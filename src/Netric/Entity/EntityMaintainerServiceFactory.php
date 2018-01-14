<?php

/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2017 Aereus
 */
namespace Netric\Entity;

use Netric\ServiceManager\AccountServiceLocatorInterface;
use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\FileSystem\FileSystem;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\EntityLoaderFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;

/**
 * Create a service for delivering mail
 */
class EntityMaintainerServiceFactory implements AccountServiceLocatorInterface
{
    /**
     * Service creation factory
     *
     * @param AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return EntityMaintainerService
     */
    public function createService(AccountServiceManagerInterface $sl)
    {
        $log = $sl->get("Log");
        $entityLoader = $sl->get(EntityLoaderFactory::class);
        $entityIndex = $sl->get(IndexFactory::class);
        $entityDefinitionLoader = $sl->get(EntityDefinitionLoaderFactory::class);
        $fileSystem = $sl->get(FileSystem::class);
        return new EntityMaintainerService($log, $entityLoader, $entityDefinitionLoader, $entityIndex, $fileSystem);
    }
}
