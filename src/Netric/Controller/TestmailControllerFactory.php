<?php

declare(strict_types=1);

namespace Netric\Controller;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;

/**
 * Construct TestControler with any dependencies
 */
class TestmailControllerFactory implements FactoryInterface
{
    /**
     * Invoke with the service container
     *
     * @param ServiceContainerInterface $container
     * @return TestmailController
     */
    public function __invoke(ServiceContainerInterface $container)
    {
        return new TestmailController();
    }
}
