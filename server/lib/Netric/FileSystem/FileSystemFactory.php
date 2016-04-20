<?php
/**
 * Factory used to initialize the netric filesystem
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\FileSystem;

use Netric\ServiceManager;

/**
 * Create a file system service
 * @package Netric\FileSystem
 */
class FileSystemFactory implements ServiceManager\AccountServiceLocatorInterface
{
    /**
     * Service creation factory
     *
     * @param \Netric\ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return FileSystem
     */
    public function createService(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $fileStore = $sl->get('Netric\FileSystem\FileStore\FileStore');
        $user = $sl->getAccount()->getUser();
        $entityLoader = $sl->get("EntityLoader");
        $dataMapper = $sl->get("Entity_DataMapper");
        $entityIndex = $sl->get("EntityQuery_Index");

        return new FileSystem($fileStore, $user, $entityLoader, $dataMapper, $entityIndex);
    }
}
