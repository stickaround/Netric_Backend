<?php
namespace NetricTest\WorkerMan;

use PHPUnit\Framework\TestCase;

class WorkerServiceFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getApplication()->getServiceManager();
        $this->assertInstanceOf(
            'Netric\WorkerMan\WorkerService',
            $sm->get('Netric\WorkerMan\WorkerService')
        );
    }
}