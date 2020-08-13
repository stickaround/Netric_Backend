<?php

namespace NetricTest\Controller;

use Netric\Account\Account;
use Netric\FileSystem\FileSystem;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Controller\EmailControllerFactory;
use Netric\Controller\EmailController;

/**
 * Test calling the email controller factory
 *
 * @group integration
 */
class EmailControllerFactoryTest extends TestCase
{
    /**
     * Make sure the factory works
     */
    public function testGet()
    {
        $account = Bootstrap::getAccount();
        $serviceManager = $account->getServiceManager();
        $controllerFactory = new EmailControllerFactory();
        $emailController = $controllerFactory->get($serviceManager);
        $this->assertInstanceOf(EmailController::class, $emailController);
    }
}
