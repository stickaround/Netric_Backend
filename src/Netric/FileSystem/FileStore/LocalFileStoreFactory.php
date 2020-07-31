<?php

/**
 * Factory used to initialize the netric filesystem filestore
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\FileSystem\FileStore;

use Netric\Config\ConfigFactory;
use Netric\Entity\DataMapper\EntityDataMapperFactory;
use Netric\ServiceManager\AccountServiceFactoryInterface;
use Netric\ServiceManager\AccountServiceManagerInterface;

/**
 * Create a file system storage service
 */
class LocalFileStoreFactory implements AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param AccountServiceManagerInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return LocalFileStore
     */
    public function createService(AccountServiceManagerInterface $serviceLocator)
    {
        $config = $serviceLocator->get(ConfigFactory::class);
        $dataPath = $config->data_path;
        $accountId = $serviceLocator->getAccount()->getAccountId();
        $dataMapper = $serviceLocator->get(EntityDataMapperFactory::class);

        return new LocalFileStore($accountId, $dataPath, $dataMapper);
    }
}
