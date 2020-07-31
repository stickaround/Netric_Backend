<?php

namespace Netric\FileSystem\FileStore;

use Netric\Config\ConfigFactory;
use Netric\ServiceManager;
use Netric\Entity\DataMapper\EntityDataMapperFactory;
use Netric\ServiceManager\AccountServiceManagerInterface;

/**
 * Create a file system storage service that uses aereus network storage
 */
class AnsFileStoreFactory implements ServiceManager\AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param AccountServiceManagerInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return LocalFileStore
     */
    public function createService(AccountServiceManagerInterface $serviceLocator)
    {
        $accountId = $serviceLocator->getAccount()->getAccountId();
        $dataMapper = $serviceLocator->get(EntityDataMapperFactory::class);

        $config = $serviceLocator->get(ConfigFactory::class);
        $ansServer = $config->alib->ans_server;
        $ansAccount = $config->alib->ans_account;
        $ansPassword = $config->alib->ans_password;

        $tmpPath = $config->data_path . "/" . "tmp";

        return new AnsFileStore($accountId, $dataMapper, $ansServer, $ansAccount, $ansPassword, $tmpPath);
    }
}
