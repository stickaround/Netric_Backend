<?php
/**
 * Test the ActivityLog service factory
 */
namespace NetricTest\Settings;

use Netric\Entity;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Entity\ActivityLog;
use Netric\Entity\ActivityLogFactory;

class ActivityLogFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $this->assertInstanceOf(
            ActivityLog::class,
            $sm->get(ActivityLogFactory::class)
        );
    }
}
