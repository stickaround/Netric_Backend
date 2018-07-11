<?php
/**
 * Factory used to initialize an image resizer service for netric files
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\FileSystem;

use Netric\ServiceManager;
use Netric\Config\ConfigFactory;

/**
 * Create an image resizer service
 *
 * @package Netric\FileSystem
 */
class ImageResizerFactory implements ServiceManager\AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param \Netric\ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return ImageResizer
     */
    public function createService(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $fileSystem = $sl->get('Netric\FileSystem\FileSystem');
        $config = $sl->get(ConfigFactory::class);
        $localTempPath = $config->data_path . '/tmp';

        // Make sure that the temp directory exists
        if (!file_exists($localTempPath)) {
            throw new \RuntimeException("Temp path $localTempPath does not exist");
        }

        return new ImageResizer($fileSystem, $localTempPath);
    }
}
