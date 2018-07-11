<?php
/**
 * Test the RecurrenceIdentityMapper service factory
 */

namespace NetricTest\Entity\Recurrence;

use Netric;
use PHPUnit\Framework\TestCase;

class RecurrenceIdentityMapperFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $im = $sm->get("RecurrenceIdentityMapper"); // is mapped to this name
        $this->assertInstanceOf('Netric\Entity\Recurrence\RecurrenceIdentityMapper', $im);
    }
}
