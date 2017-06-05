<?php

namespace NetricTest\Log;

use Netric;

use PHPUnit\Framework\TestCase;

class LogFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            'Netric\Log\Log',
            $sm->get('Log')
        );

        $this->assertInstanceOf(
            'Netric\Log\Log',
            $sm->get('Netric\Log\Log')
        );
    }
}