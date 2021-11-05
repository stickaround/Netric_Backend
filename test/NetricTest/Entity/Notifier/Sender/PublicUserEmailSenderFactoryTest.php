<?php

/**
 * Test the NotifierFactory factory
 */

namespace NetricTest\Entity\Notifier\Sender;

use Netric\Entity\Notifier\Sender\PublicUserEmailSender;
use Netric\Entity\Notifier\Sender\PublicUserEmailSenderFactory;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;

class PublicUserEmailSenderFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $this->assertInstanceOf(
            PublicUserEmailSender::class,
            $sm->get(PublicUserEmailSenderFactory::class)
        );
    }
}
