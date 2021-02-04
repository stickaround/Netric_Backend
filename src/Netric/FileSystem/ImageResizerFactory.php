<?php
/**
 * Factory used to initialize an image resizer service for netric files
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\FileSystem;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Config\ConfigFactory;
use Netric\FileSystem\FileSystemFactory;

/**
 * Create an image resizer service
 *
 * @package Netric\FileSystem
 */
class ImageResizerFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return ImageResizer
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
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
