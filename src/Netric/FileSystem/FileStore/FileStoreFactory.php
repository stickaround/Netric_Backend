<?php
/**
 * Factory used to initialize the netric filesystem filestore
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\FileSystem\FileStore;

use Netric\ServiceManager;
use Netric\Config\ConfigFactory;
use Netric\FileSystem\FileStore\MogileFileStoreFactory;
use Netric\FileSystem\FileStore\LocalFileStoreFactory;

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
        $config = $sl->get(ConfigFactory::class);
        $store = $config->get('files')->get('store');

        if ($store === "mogile") {
            return $sl->get(MogileFileStoreFactory::class);
        } else {
            return $sl->get(LocalFileStoreFactory::class);
        }
    }
}
