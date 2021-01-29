<?php

namespace NetricTest\Controller;

use Netric\Account\Account;
use Netric\FileSystem\FileSystem;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Controller\FilesControllerFactory;
use Netric\Controller\FilesController;

/**
 * Test calling the entity query controller factory
 *
 * @group integration
 */
class FilesControllerFactoryTest extends TestCase
{
    /**
     * Make sure the factory works
     */
    public function testGet()
    {
        $account = Bootstrap::getAccount();
        $serviceManager = $account->getServiceManager();
        $controllerFactory = new FilesControllerFactory();
        $filesController = $controllerFactory->get($serviceManager);
        $this->assertInstanceOf(FilesController::class, $filesController);
    }
}
