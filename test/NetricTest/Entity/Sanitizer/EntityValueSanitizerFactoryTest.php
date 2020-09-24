<?php

/**
 * Test an EntityValueSanitizer Factory
 */

namespace NetricTest\Entity;

use Netric\Entity;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Entity\EntityValueSanitizer;
use Netric\Entity\EntityValueSanitizerFactory;

class EntityValueSanitizerFactoryTest extends TestCase
{
    public function testFactory()
    {
        $account = Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            EntityValueSanitizer::class,
            $sm->get(EntityValueSanitizerFactory::class)
        );
    }
}
