<?php

declare(strict_types=1);

namespace NetricTest\Mail;

use PHPUnit\Framework\TestCase;
use Netric\Mail\MailSystem;
use Netric\Mail\MailSystemFactory;

/**
 * @group integration
 */
class MailSystemFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sl = $account->getServiceManager();
        $this->assertInstanceOf(
            MailSystem::class,
            $sl->get(MailSystemFactory::class)
        );
    }
}
