<?php

namespace NetricTest\Controller;

use Netric\Account\Account;
use Netric\FileSystem\FileSystem;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Controller\AuthenticationControllerFactory;
use Netric\Controller\AuthenticationController;

/**
 * Test calling the browser view controller factory
 *
 * @group integration
 */
class AuthenticationControllerFactoryTest extends TestCase
{
    /**
     * Make sure the factory works
     */
    public function testGet()
    {
        $account = Bootstrap::getAccount();
        $serviceManager = $account->getServiceManager();
        $controllerFactory = new AuthenticationControllerFactory();
        $browserViewController = $controllerFactory->get($serviceManager);
        $this->assertInstanceOf(AuthenticationController::class, $browserViewController);
    }
}
