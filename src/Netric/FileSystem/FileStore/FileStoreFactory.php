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
class FileStoreFactory implements ServiceManager\AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param \Netric\ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return FileStoreInterface
     */
    public function createService(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $config = $sl->get('Netric\Config\Config');
        $store = $config->get('files')->get('store');

        $fileStore = 'Netric\FileSystem\FileStore';
        $fileStore .= ($store === "mogile") ? '\MogileFileStore' : '\LocalFileStore';

        return $sl->get($fileStore);
    }
}
