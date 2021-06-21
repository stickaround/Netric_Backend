<?php

namespace NetricTest\Handler;

use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Handler\AuthenticationHandler;
use Netric\Handler\AuthenticationHandlerFactory;

/**
 * Test calling the browser view controller factory
 *
 * @group integration
 */
class AuthenticationHandlerFactoryTest extends TestCase
{
    /**
     * Make sure the factory works
     */
    public function testGet()
    {
        $account = Bootstrap::getAccount();
        $serviceManager = $account->getServiceManager();
        $handler = $serviceManager->get(AuthenticationHandlerFactory::class);
        $this->assertInstanceOf(AuthenticationHandler::class, $handler);
    }
}
