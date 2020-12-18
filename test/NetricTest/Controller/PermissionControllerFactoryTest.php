<?php

namespace NetricTest\Controller;

use Netric\Account\Account;
use Netric\FileSystem\FileSystem;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Controller\PermissionControllerFactory;
use Netric\Controller\PermissionController;

/**
 * Test calling the permission controller factory
 *
 * @group integration
 */
class PermissionControllerFactoryTest extends TestCase
{
    /**
     * Make sure the factory works
     */
    public function testGet()
    {
        $account = Bootstrap::getAccount();
        $serviceManager = $account->getServiceManager();
        $controllerFactory = new PermissionControllerFactory();
        $permissionController = $controllerFactory->get($serviceManager);
        $this->assertInstanceOf(PermissionController::class, $permissionController);
    }
}
