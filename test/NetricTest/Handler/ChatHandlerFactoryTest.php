<?php

declare(strict_types=1);

namespace NetricTest\Handler;

use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Handler\ChatHandler;
use Netric\Handler\ChatHandlerFactory;

/**
 * Test calling the browser view controller factory
 *
 * @group integration
 */
class ChatHandlerFactoryTest extends TestCase
{
    /**
     * Make sure the factory works
     */
    public function testGet()
    {
        $account = Bootstrap::getAccount();
        $serviceManager = $account->getServiceManager();
        $handler = $serviceManager->get(ChatHandlerFactory::class);
        $this->assertInstanceOf(ChatHandler::class, $handler);
    }
}
