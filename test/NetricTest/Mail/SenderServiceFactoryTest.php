<?php

namespace NetricTest\Mail;

use PHPUnit\Framework\TestCase;
use Netric\Mail\SenderService;
use Netric\Mail\SenderServiceFactory;

/**
 * @group integration
 */
class SenderServiceFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sl = $account->getServiceManager();
        $this->assertInstanceOf(
            SenderService::class,
            $sl->get(SenderServiceFactory::class)
        );
    }
}
