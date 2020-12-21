<?php

namespace NetricTest\Controller;

use Netric\Account\Account;
use Netric\FileSystem\FileSystem;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Controller\EntityQueryControllerFactory;
use Netric\Controller\EntityQueryController;

/**
 * Test calling the entity query controller factory
 *
 * @group integration
 */
class EntityQueryControllerFactoryTest extends TestCase
{
    /**
     * Make sure the factory works
     */
    public function testGet()
    {
        $account = Bootstrap::getAccount();
        $serviceManager = $account->getServiceManager();
        $controllerFactory = new EntityQueryControllerFactory();
        $entityQueryController = $controllerFactory->get($serviceManager);
        $this->assertInstanceOf(EntityQueryController::class, $entityQueryController);
    }
}
