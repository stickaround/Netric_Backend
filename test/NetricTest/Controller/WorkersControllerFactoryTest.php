<?php

namespace NetricTest\Controller;

use Netric\Account\Account;
use Netric\FileSystem\FileSystem;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Controller\WorkersControllerFactory;
use Netric\Controller\WorkersController;

/**
 * Test calling the workers controller factory
 *
 * @group integration
 */
class WorkersControllerFactoryTest extends TestCase
{
    /**
     * Make sure the factory works
     */
    public function testGet()
    {
        $account = Bootstrap::getAccount();
        $serviceManager = $account->getServiceManager();
        $controllerFactory = new WorkersControllerFactory();
        $workersController = $controllerFactory->get($serviceManager);
        $this->assertInstanceOf(WorkersController::class, $workersController);
    }
}
