<?php

declare(strict_types=1);

namespace Netric\Config;

use Aereus\Config\ConfigLoader;
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
        $configLoader = new ConfigLoader();
        $appEnv = (getenv('APPLICATION_ENV')) ? getenv('APPLICATION_ENV') : 'production';

        // Setup the new config
        return $configLoader->fromFolder(__DIR__ . '/../../../config', $appEnv);
    }
}
