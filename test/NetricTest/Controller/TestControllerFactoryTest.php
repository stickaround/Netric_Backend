<?php

namespace NetricTest\Controller;

use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Controller\TestControllerFactory;
use Netric\Controller\TestController;

/**
 * Test calling the test controller factory
 * 
 * @group integration
 */
class TestControllerFactoryTest extends TestCase
{
    /**
     * Make sure the factory works
     */
    public function testGet()
    {
        $account = Bootstrap::getAccount();
        $serviceManager = $account->getServiceManager();
        $controllerFactory = new TestControllerFactory();
        $emailController = $controllerFactory->get($serviceManager);
        $this->assertInstanceOf(TestController::class, $emailController);
    }
}
