<?php

declare(strict_types=1);

namespace Netric\Db\Relational;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Config\ConfigFactory;
use Netric\Db\Relational\RelationalDbFactory;

/**
 * Create a service that returns a handle to an account database
 */
class RelationalDbContainerFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return RelationalDbInterface
     */
    public function __invoke(ServiceContainerInterface $servoceLocator)
    {
        $database = $servoceLocator->get(RelationalDbFactory::class);
        return new RelationalDbContainer($database);
    }
}
