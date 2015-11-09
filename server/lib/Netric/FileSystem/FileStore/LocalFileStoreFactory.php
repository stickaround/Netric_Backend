<?php
/**
 * Factory used to initialize the netric filesystem filestore
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\FileSystem\FileStore;

use Netric\ServiceManager;

/**
 * Create a file system storage service
 */
class LocalFileStoreFactory implements ServiceManager\ServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param \Netric\ServiceManager\ServiceLocatorInterface $sl ServiceLocator for injecting dependencies
     * @return LocalFileStore
     */
    public function createService(ServiceManager\ServiceLocatorInterface $sl)
    {
        $config = $sl->get("Config");
        $config = $sl->get("Config");
        $dataPath = $config->data_path;
        $accountId = $sl->getAccount()->getId();
        $dataMapper = $sl->get("Entity_DataMapper");

        return new LocalFileStore($accountId, $dataPath, $dataMapper);
    }
}
