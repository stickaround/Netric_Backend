<?php

declare(strict_types=1);

namespace Netric\Db\Relational;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Config\ConfigFactory;
use Netric\Db\Relational\RelationalDbFactory;

/**
 * Create a service that returns a handle to an account database
 */
class RelationalDbContainerFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $servoceLocator ServiceLocator for injecting dependencies
     * @return RelationalDbInterface
     */
    public function createService(ServiceLocatorInterface $servoceLocator)
    {
        $database = $servoceLocator->get(RelationalDbFactory::class);
        return new RelationalDbContainer($database);
    }
}
