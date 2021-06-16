<?php
/**
 * Factory used to initialize an image resizer service for netric files
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\FileSystem;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Config\ConfigFactory;
use Netric\FileSystem\FileSystemFactory;

/**
 * Create an image resizer service
 *
 * @package Netric\FileSystem
 */
class ImageResizerFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return ImageResizer
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $fileSystem = $serviceLocator->get(FileSystemFactory::class);
        $config = $serviceLocator->get(ConfigFactory::class);
        $localTempPath = $config->data_path . '/tmp';

        // Make sure that the temp directory exists
        if (!file_exists($localTempPath)) {
            throw new \RuntimeException("Temp path $localTempPath does not exist");
        }

        return new ImageResizer($fileSystem, $localTempPath);
    }
}
