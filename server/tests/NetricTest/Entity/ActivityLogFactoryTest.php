<?php
/**
 * Test the ActivityLog service factory
 */
namespace NetricTest\Settings;

use Netric\Entity;
use PHPUnit\Framework\TestCase;

class ActivityLogFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $this->assertInstanceOf(
            'Netric\Entity\ActivityLog',
            $sm->get('Netric\Entity\ActivityLog')
        );
    }
}