<?php
/**
 * Test the EntitySeriesWriter service factory
 */

namespace NetricTest\Entity\Recurrence;

use Netric;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Entity\Recurrence\RecurrenceSeriesManager;
use Netric\Entity\Recurrence\RecurrenceSeriesManagerFactory;

class RecurrenceSeriesManagerFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $seriesWriter = $sm->get(RecurrenceSeriesManagerFactory::class);
        $this->assertInstanceOf(RecurrenceSeriesManager::class, $seriesWriter);
    }
}
