<?php
/**
 * Test the RecurrenceDataMapperFactoryTest service factory
 */

namespace NetricTest\Entity\Recurrence;

use Netric;
use PHPUnit\Framework\TestCase;

class RecurrenceDataMapperFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $dm = $sm->get("RecurrenceDataMapper"); // is mapped to this name
        $this->assertInstanceOf('Netric\Entity\Recurrence\RecurrenceDataMapper', $dm);
    }
}