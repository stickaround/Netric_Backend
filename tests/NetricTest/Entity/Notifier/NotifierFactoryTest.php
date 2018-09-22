<?php
/**
 * Test the NotifierFactory factory
 */
namespace NetricTest\Entity\Notifier;

use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Entity\Notifier\Notifier;
use Netric\Entity\Notifier\NotifierFactory;

class NotifierFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $this->assertInstanceOf(
            Notifier::class,
            $sm->get(NotifierFactory::class)
        );
    }
}
