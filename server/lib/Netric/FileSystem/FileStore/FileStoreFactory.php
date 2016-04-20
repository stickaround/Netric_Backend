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
class FileStoreFactory implements ServiceManager\AccountServiceLocatorInterface
{
    /**
     * Service creation factory
     *
     * @param \Netric\ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return FileStoreInterface
     */
    public function createService(ServiceManager\AccountServiceManagerInterface $sl)
    {
        // TODO: get the default FileStore based on config values
        //       but for now just use local
        //
        // $config = $sl->get("Config");

        return $sl->get('Netric\FileSystem\FileStore\LocalFileStore');
    }
}
