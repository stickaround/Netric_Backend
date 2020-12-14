<?php

namespace NetricTest\Controller;

use Netric\Account\Account;
use Netric\FileSystem\FileSystem;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Controller\EntityControllerFactory;
use Netric\Controller\EntityController;

/**
 * Test calling the entity controller factory
 *
 * @group integration
 */
class EntityControllerFactoryTest extends TestCase
{
    /**
     * Make sure the factory works
     */
    public function testGet()
    {
        $account = Bootstrap::getAccount();
        $serviceManager = $account->getServiceManager();
        $controllerFactory = new EntityControllerFactory();
        $entityController = $controllerFactory->get($serviceManager);
        $this->assertInstanceOf(EntityController::class, $entityController);
    }
}
