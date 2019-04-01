<?php
/**
 * Test the RecurrenceIdentityMapper service factory
 */

namespace NetricTest\Entity\Recurrence;

use Netric;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Entity\Recurrence\RecurrenceIdentityMapper;
use Netric\Entity\Recurrence\RecurrenceIdentityMapperFactory;

class RecurrenceIdentityMapperFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $im = $sm->get(RecurrenceIdentityMapperFactory::class); // is mapped to this name
        $this->assertInstanceOf(RecurrenceIdentityMapper::class, $im);
    }
}
