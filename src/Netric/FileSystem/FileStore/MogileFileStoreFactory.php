<?php
namespace Netric\FileSystem\FileStore;

use Netric\ServiceManager;
use MogileFs;
use Netric\Config\ConfigFactory;
use Netric\Entity\DataMapper\DataMapperFactory;

/**
 * Create a file system storage service that uses aereus network storage
 */
class MogileFileStoreFactory implements ServiceManager\AccountServiceLocatorInterface
{
    /**
     * Service creation factory
     *
     * @param \Netric\ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return MogileFileStore
     */
    public function createService(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $accountId = $sl->getAccount()->getId();
        $dataMapper = $sl->get(DataMapperFactory::class);

        $config = $sl->get(ConfigFactory::class);
        $tmpPath = $config->data_path . "/" . "tmp";

        // Set the port
        $port = ($config->files->port) ? $config->files->port : 7001;

        // Establish mogile connection
        $mfsClient = new MogileFs();
        $mfsClient->connect($config->files->server, $port, $config->files->account);

        return new MogileFileStore(
            $accountId,
            $mfsClient,
            $dataMapper,
            $tmpPath
        );
    }
}
