<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Config;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Handle setting up the config service
 */
class ConfigFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $sl ServiceLocator for injecting dependencies
     * @return Config
     */
    public function createService(ServiceLocatorInterface $sl)
    {
        $applicationEnvironment = (getenv('APPLICATION_ENV')) ? getenv('APPLICATION_ENV') : "production";
        echo "\nConfig loading env: $applicationEnvironment\n";
        return ConfigLoader::fromFolder(
            __DIR__ . "/../../../config",
            $applicationEnvironment
        );
    }
}
