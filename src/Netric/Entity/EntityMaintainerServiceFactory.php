<?php

/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2017 Aereus
 */

namespace Netric\Entity;

use Netric\Account\AccountContainerFactory;
use Netric\ServiceManager\AccountServiceFactoryInterface;
use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\FileSystem\FileSystemFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\Log\LogFactory;

/**
 * Create a service for delivering mail
 */
class EntityMaintainerServiceFactory implements AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return EntityMaintainerService
     */
    public function createService(AccountServiceManagerInterface $sl)
    {
        $log = $sl->get(LogFactory::class);
        $entityLoader = $sl->get(EntityLoaderFactory::class);
        $entityIndex = $sl->get(IndexFactory::class);
        $entityDefinitionLoader = $sl->get(EntityDefinitionLoaderFactory::class);
        $fileSystem = $sl->get(FileSystemFactory::class);
        $accountContainer = $sl->get(AccountContainerFactory::class);
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
