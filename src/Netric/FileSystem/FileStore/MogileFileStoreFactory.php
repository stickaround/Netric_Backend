<?php
namespace Netric\FileSystem\FileStore;

use Netric\ServiceManager;
use MogileFs;
use Netric\Config\ConfigFactory;
use Netric\Entity\EntityLoaderFactory;

/**
 * Create a file system storage service that uses aereus network storage
 */
class MogileFileStoreFactory implements ServiceManager\AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param \Netric\ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return MogileFileStore
     */
    public function createService(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $accountId = $sl->getAccount()->getAccountId();
        $entityLoader = $sl->get(EntityLoaderFactory::class);

        $config = $sl->get(ConfigFactory::class);
        $tmpPath = $config->data_path . "/" . "tmp";

        return new MogileFileStore(
            $accountId,
            $entityLoader,
            $tmpPath,
            $config->files->server,
            $config->files->account,
            $config->files->port
        );
    }
}
