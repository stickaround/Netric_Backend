<?php

namespace NetricTest\Controller;

use Netric\Account\Account;
use Netric\FileSystem\FileSystem;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Controller\HealthControllerFactory;
use Netric\Controller\HealthController;

/**
 * Test calling the entity query controller factory
 *
 * @group integration
 */
class HealthControllerFactoryTest extends TestCase
{
    /**
     * Make sure the factory works
     */
    public function testGet()
    {
        $account = Bootstrap::getAccount();
        $serviceManager = $account->getServiceManager();
        $controllerFactory = new HealthControllerFactory();
        $healthController = $controllerFactory->get($serviceManager);
        $this->assertInstanceOf(HealthController::class, $healthController);
    }
}
