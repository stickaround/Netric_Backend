<?php
namespace NetricTest\WorkerMan;

use PHPUnit\Framework\TestCase;

class ConfigFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $this->assertInstanceOf(
            'Netric\Config\Config',
            $sm->get('Netric\Config\Config')
        );
    }
}