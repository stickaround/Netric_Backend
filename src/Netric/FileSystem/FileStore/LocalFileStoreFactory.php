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
use netric\Entity\DataMapper\DataMapperFactory;

/**
 * Create a file system storage service
 */
class LocalFileStoreFactory implements ServiceManager\AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param \Netric\ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return LocalFileStore
     */
    public function createService(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $config = $sl->get(ConfigFactory::class);
        $dataPath = $config->data_path;
        $accountId = $sl->getAccount()->getId();
        $dataMapper = $sl->get(DataMapperFactory::class);

        return new LocalFileStore($accountId, $dataPath, $dataMapper);
    }
}
