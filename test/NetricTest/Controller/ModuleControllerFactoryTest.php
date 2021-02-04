<?php

namespace NetricTest\Controller;

use Netric\Account\Account;
use Netric\FileSystem\FileSystem;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Controller\ModuleControllerFactory;
use Netric\Controller\ModuleController;

/**
 * Test calling the module controller factory
 *
 * @group integration
 */
class ModuleControllerFactoryTest extends TestCase
{
    /**
     * Make sure the factory works
     */
    public function testGet()
    {
        $account = Bootstrap::getAccount();
        $serviceManager = $account->getServiceManager();
        $controllerFactory = new ModuleControllerFactory();
        $moduleController = $controllerFactory->get($serviceManager);
        $this->assertInstanceOf(ModuleController::class, $moduleController);
    }
}
