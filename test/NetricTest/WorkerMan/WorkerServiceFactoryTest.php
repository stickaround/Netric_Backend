<?php

namespace NetricTest\WorkerMan;

use PHPUnit\Framework\TestCase;
use Netric\WorkerMan\WorkerService;
use Netric\WorkerMan\WorkerServiceFactory;
use NetricTest\Bootstrap;

/**
 * @group integration
 */
class WorkerServiceFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = Bootstrap::getAccount();
        $sm = $account->getApplication()->getServiceManager();
        $this->assertInstanceOf(
            WorkerService::class,
            $sm->get(WorkerServiceFactory::class)
        );
    }
}
