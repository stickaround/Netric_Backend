<?php

namespace NetricTest\Log;

use Netric;
use Netric\Log\Log;
use Netric\Log\LogFactory;
use PHPUnit\Framework\TestCase;

class LogFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        // Get by alias (old method)
        $this->assertInstanceOf(
            Log::class,
            $sm->get(LogFactory::class)
        );

        // Get by factory (newer and preferred method)
        $this->assertInstanceOf(
            Log::class,
            $sm->get(LogFactory::class)
        );
    }
}
