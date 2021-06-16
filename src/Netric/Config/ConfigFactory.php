<?php

declare(strict_types=1);

namespace Netric\Config;

use Aereus\Config\ConfigLoader;
use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;

/**
 * Handle setting up the config service
 */
class ConfigFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return Config
     */
    public function __invoke(ServiceContainerInterface $sl)
    {
        $configLoader = new ConfigLoader();
        $appEnv = (getenv('APPLICATION_ENV')) ? getenv('APPLICATION_ENV') : 'production';

        // Setup the new config
        return $configLoader->fromFolder(__DIR__ . '/../../../config', $appEnv);
    }
}
