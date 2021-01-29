<?php

namespace NetricTest\Controller;

use Netric\Account\Account;
use Netric\FileSystem\FileSystem;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Controller\SetupControllerFactory;
use Netric\Controller\SetupController;

/**
 * Test calling the setup controller factory
 *
 * @group integration
 */
class SetupControllerFactoryTest extends TestCase
{
    /**
     * Make sure the factory works
     */
    public function testGet()
    {
        $account = Bootstrap::getAccount();
        $serviceManager = $account->getServiceManager();
        $controllerFactory = new SetupControllerFactory();
        $setupController = $controllerFactory->get($serviceManager);
        $this->assertInstanceOf(SetupController::class, $setupController);
    }
}
