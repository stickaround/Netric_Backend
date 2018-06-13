<?php

namespace NetricTest\EntitySync;

use Netric;

use PHPUnit\Framework\TestCase;

class EntitySyncFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            'Netric\EntitySync\EntitySync',
            $sm->get('EntitySync')
        );

        $this->assertInstanceOf(
            'Netric\EntitySync\EntitySync',
            $sm->get('Netric\EntitySync\EntitySync')
        );
    }
}
