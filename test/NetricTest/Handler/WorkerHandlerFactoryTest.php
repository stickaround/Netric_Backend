<?php

declare(strict_types=1);

namespace NetricTest\Handler;

use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Handler\WorkerHandler;
use Netric\Handler\WorkerHandlerFactory;

/**
 * Test calling the browser view controller factory
 *
 * @group integration
 */
class WorkerHandlerFactoryTest extends TestCase
{
    /**
     * Make sure the factory works
     */
    public function testGet()
    {
        $account = Bootstrap::getAccount();
        $serviceManager = $account->getServiceManager();
        $handler = $serviceManager->get(WorkerHandlerFactory::class);
        $this->assertInstanceOf(WorkerHandler::class, $handler);
    }
}
