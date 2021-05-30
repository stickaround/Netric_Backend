<?php

namespace NetricTest\Controller;

use Netric\Account\Account;
use Netric\FileSystem\FileSystem;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Controller\TestControllerFactory;
use Netric\Controller\TestController;

use Aereus\ServiceContainer\ServiceContainer;

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
        // Instantiate the ServiceContainer
        $serviceContainer = new ServiceContainer();

        $emailController = $serviceContainer->get(TestControllerFactory::class);
        $this->assertInstanceOf(TestController::class, $emailController);
        
    }
}
