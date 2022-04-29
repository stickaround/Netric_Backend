<?php

declare(strict_types=1);

namespace NetricTest\Handler;

use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Handler\EntityQueryHandler;
use Netric\Handler\EntityQueryHandlerFactory;

/**
 * Make sure we can create the entity query handler
 *
 * @group integration
 */
class EntityQueryHandlerFactoryTest extends TestCase
{
    /**
     * Make sure the factory works
     */
    public function testGet()
    {
        $account = Bootstrap::getAccount();
        $serviceManager = $account->getServiceManager();
        $handler = $serviceManager->get(EntityQueryHandlerFactory::class);
        $this->assertInstanceOf(EntityQueryHandler::class, $handler);
    }
}
