<?php
/**
 * Test the NotifierFactory factory
 */
namespace NetricTest\Entity\Notifier;

use PHPUnit\Framework\TestCase;

class NotifierFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $this->assertInstanceOf(
            'Netric\Entity\Notifier\Notifier',
            $sm->get('Netric\Entity\Notifier\Notifier')
        );
    }
}
