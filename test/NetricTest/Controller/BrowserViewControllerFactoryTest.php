<?php

namespace NetricTest\Controller;

use Netric\Account\Account;
use Netric\FileSystem\FileSystem;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Controller\BrowserViewControllerFactory;
use Netric\Controller\BrowserViewController;

/**
 * Test calling the browser view controller factory
 *
 * @group integration
 */
class BrowserViewControllerFactoryTest extends TestCase
{
    /**
     * Make sure the factory works
     */
    public function testGet()
    {
        $account = Bootstrap::getAccount();
        $serviceManager = $account->getServiceManager();
        $controllerFactory = new BrowserViewControllerFactory();
        $browserViewController = $controllerFactory->get($serviceManager);
        $this->assertInstanceOf(BrowserViewController::class, $browserViewController);
    }
}
