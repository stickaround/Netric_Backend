<?php

declare(strict_types=1);

namespace NetricTest\Handler;

use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Handler\EntityHandler;
use Netric\Handler\EntityHandlerFactory;

/**
 * Test calling the browser view controller factory
 *
 * @group integration
 */
class EntityHandlerFactoryTest extends TestCase
{
    /**
     * Make sure the factory works
     */
    public function testGet()
    {
        $account = Bootstrap::getAccount();
        $serviceManager = $account->getServiceManager();
        $handler = $serviceManager->get(EntityHandlerFactory::class);
        $this->assertInstanceOf(EntityHandler::class, $handler);
    }
}
