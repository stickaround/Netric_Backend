<?php
/**
 * Factory used to initialize the netric filesystem
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\FileSystem;

use Netric\Entity\EntityLoaderFactory;
use Netric\FileSystem\FileStore\FileStoreFactory;
use Netric\Entity\DataMapper\DataMapperFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\ServiceManager\AccountServiceFactoryInterface;

/**
 * Create a file system service
 * @package Netric\FileSystem
 */
class FileSystemFactory implements AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return FileSystem
     */
    public function createService(AccountServiceManagerInterface $sl)
    {
        $fileStore = $sl->get(FileStoreFactory::class);
        $user = $sl->getAccount()->getUser();
        $entityLoader = $sl->get(EntityLoaderFactory::class);
        $dataMapper = $sl->get(DataMapperFactory::class);
        $entityIndex = $sl->get(IndexFactory::class);

        return new FileSystem($fileStore, $user, $entityLoader, $dataMapper, $entityIndex);
    }
}
