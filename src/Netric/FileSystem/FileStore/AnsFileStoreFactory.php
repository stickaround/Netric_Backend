<?php
/**
 * Factory used to initialize the netric filesystem filestore
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\FileSystem\FileStore;

use Netric\Config\ConfigFactory;
use Netric\ServiceManager;
use Netric\Entity\DataMapper\DataMapperFactory;

/**
 * Create a file system storage service that uses aereus network storage
 */
class AnsFileStoreFactory implements ServiceManager\AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param \Netric\ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return LocalFileStore
     */
    public function createService(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $accountId = $sl->getAccount()->getId();
        $dataMapper = $sl->get(DataMapperFactory::class);

        $config = $sl->get(ConfigFactory::class);
        $ansServer = $config->alib->ans_server;
        $ansAccount = $config->alib->ans_account;
        $ansPassword = $config->alib->ans_password;

        $tmpPath = $config->data_path . "/" . "tmp";

        return new AnsFileStore($accountId, $dataMapper, $ansServer, $ansAccount, $ansPassword, $tmpPath);
    }
}